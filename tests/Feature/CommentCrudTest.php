<?php

namespace Tests\Feature;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_comment_on_a_published_post_without_an_account(): void
    {
        $post = Post::factory()->create();

        $response = $this->post(route('comments.store', $post), [
            'comment' => 'This public explanation helped me prepare for the assessment conversation.',
            'user_id' => User::factory()->create()->getKey(),
            'status' => CommentStatus::Hidden->value,
        ]);

        $comment = Comment::query()->sole();

        $response->assertRedirect(route('posts.show', $post).'#comment-'.$comment->getKey());
        $this->assertNull($comment->user_id);
        $this->assertSame(CommentStatus::Active, $comment->status);
    }

    public function test_an_authenticated_comment_is_associated_with_its_author(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->post(route('comments.store', $post), [
                'comment' => 'Keeping the timeline general is a useful privacy reminder.',
            ])
            ->assertRedirect();

        $this->assertTrue(Comment::query()->sole()->user->is($user));
    }

    public function test_comment_input_is_validated_and_sensitive_identifiers_are_rejected(): void
    {
        $post = Post::factory()->create();

        $this->post(route('comments.store', $post), ['comment' => ' '])
            ->assertSessionHasErrors('comment');

        $this->post(route('comments.store', $post), [
            'comment' => 'Please check claim number CLM-123456 before replying.',
        ])->assertSessionHasErrors('comment');

        $this->assertDatabaseEmpty('comments');
    }

    public function test_comments_cannot_be_added_to_draft_posts(): void
    {
        $owner = User::factory()->create();
        $draft = Post::factory()->draft()->for($owner)->create();

        $this->actingAs($owner)
            ->post(route('comments.store', $draft), ['comment' => 'This must remain private.'])
            ->assertForbidden();

        $this->assertDatabaseEmpty('comments');
    }

    public function test_guest_comment_submission_is_rate_limited(): void
    {
        $post = Post::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('comments.store', $post), [
                'comment' => "Useful public observation number {$attempt}.",
            ])->assertRedirect();
        }

        $this->post(route('comments.store', $post), [
            'comment' => 'This request exceeds the guest limit.',
        ])->assertStatus(429);

        $this->assertSame(5, Comment::query()->count());
    }

    public function test_a_comment_author_can_delete_their_comment(): void
    {
        $author = User::factory()->create();
        $comment = Comment::factory()->for($author)->create();

        $this->actingAs($author)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('posts.show', $comment->post));

        $this->assertModelMissing($comment);
    }

    public function test_a_post_owner_can_delete_another_users_or_guest_comment(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();
        $usersComment = Comment::factory()->for($post)->create();
        $guestComment = Comment::factory()->guest()->for($post)->create();

        $this->actingAs($owner)->delete(route('comments.destroy', $usersComment))->assertRedirect();
        $this->actingAs($owner)->delete(route('comments.destroy', $guestComment))->assertRedirect();

        $this->assertModelMissing($usersComment);
        $this->assertModelMissing($guestComment);
    }

    public function test_guests_and_unrelated_users_cannot_delete_comments(): void
    {
        $comment = Comment::factory()->create();

        $this->delete(route('comments.destroy', $comment))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->delete(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertModelExists($comment);
    }

    public function test_moderators_and_administrators_can_delete_any_comment(): void
    {
        $moderatorsComment = Comment::factory()->create();
        $administratorsComment = Comment::factory()->create();

        $this->actingAs(User::factory()->moderator()->create())
            ->delete(route('comments.destroy', $moderatorsComment))
            ->assertRedirect();

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('comments.destroy', $administratorsComment))
            ->assertRedirect();

        $this->assertModelMissing($moderatorsComment);
        $this->assertModelMissing($administratorsComment);
    }

    public function test_only_moderators_and_administrators_can_change_comment_status(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('comments.status.update', $comment), [
                'status' => CommentStatus::Hidden->value,
            ])->assertForbidden();

        $this->actingAs(User::factory()->moderator()->create())
            ->patch(route('comments.status.update', $comment), [
                'status' => CommentStatus::Hidden->value,
            ])->assertRedirect();

        $this->assertSame(CommentStatus::Hidden, $comment->fresh()->status);

        $this->actingAs(User::factory()->administrator()->create())
            ->patch(route('comments.status.update', $comment), [
                'status' => CommentStatus::Active->value,
            ])->assertRedirect();

        $this->assertSame(CommentStatus::Active, $comment->fresh()->status);
    }

    public function test_an_invalid_moderation_status_is_rejected(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs(User::factory()->moderator()->create())
            ->patch(route('comments.status.update', $comment), ['status' => 'removed'])
            ->assertSessionHasErrors('status');

        $this->assertSame(CommentStatus::Active, $comment->fresh()->status);
    }

    public function test_hidden_comments_are_visible_only_to_permitted_people(): void
    {
        $postOwner = User::factory()->create();
        $commentAuthor = User::factory()->create();
        $post = Post::factory()->for($postOwner)->create();
        $comment = Comment::factory()->hidden()->for($post)->for($commentAuthor)->create([
            'comment' => 'Distinct hidden moderation content.',
        ]);

        $this->get(route('posts.show', $post))->assertDontSeeText($comment->comment);
        $this->actingAs(User::factory()->create())
            ->get(route('posts.show', $post))
            ->assertDontSeeText($comment->comment);
        $this->actingAs($commentAuthor)
            ->get(route('posts.show', $post))
            ->assertSeeText($comment->comment);
        $this->actingAs($postOwner)
            ->get(route('posts.show', $post))
            ->assertSeeText($comment->comment);
        $this->actingAs(User::factory()->moderator()->create())
            ->get(route('posts.show', $post))
            ->assertSeeText($comment->comment)
            ->assertSeeText('Hidden');
    }

    public function test_comments_are_displayed_under_the_post_and_escaped(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->guest()->for($post)->create([
            'comment' => '<script>alert("unsafe")</script> This comment remains visible as text.',
        ]);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSeeText('Guest')
            ->assertSeeText($comment->comment)
            ->assertDontSee('<script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_the_post_index_counts_only_active_comments(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(2)->for($post)->create();
        Comment::factory()->hidden()->for($post)->create();

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSeeText('2 comments');
    }
}
