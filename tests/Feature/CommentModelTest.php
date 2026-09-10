<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_relationships_are_defined(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($user)->for($post)->create();

        $this->assertTrue($user->comments()->first()->is($comment));
        $this->assertTrue($post->comments()->first()->is($comment));
        $this->assertTrue($comment->user->is($user));
        $this->assertTrue($comment->post->is($post));
    }

    public function test_deleting_a_post_cascades_to_its_comments(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->create();

        $post->delete();

        $this->assertModelMissing($comment);
    }

    public function test_deleting_a_comment_author_anonymizes_the_comment(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->for($author)->create();

        $author->delete();

        $this->assertModelExists($comment);
        $this->assertNull($comment->fresh()->user_id);
    }

    public function test_visibility_scope_includes_only_permitted_comments(): void
    {
        $postOwner = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->for($postOwner)->create();
        $active = Comment::factory()->for($post)->create();
        $authorsHidden = Comment::factory()->hidden()->for($post)->for($commentAuthor)->create();
        $otherHidden = Comment::factory()->hidden()->for($post)->for($otherUser)->create();

        $this->assertEqualsCanonicalizing(
            [$active->id],
            Comment::query()->visibleTo(null)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$active->id, $authorsHidden->id],
            Comment::query()->visibleTo($commentAuthor)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$active->id, $authorsHidden->id, $otherHidden->id],
            Comment::query()->visibleTo($postOwner)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$active->id, $authorsHidden->id, $otherHidden->id],
            Comment::query()->visibleTo(User::factory()->moderator()->create())->pluck('id')->all(),
        );
    }
}
