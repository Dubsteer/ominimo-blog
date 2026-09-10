<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_and_post_relationships_are_defined(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->assertTrue($user->posts()->first()->is($post));
        $this->assertTrue($post->user->is($user));
    }

    public function test_visibility_scope_includes_only_permitted_posts(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $published = Post::factory()->for($otherUser)->create();
        $ownedDraft = Post::factory()->draft()->for($owner)->create();
        $otherDraft = Post::factory()->draft()->for($otherUser)->create();

        $this->assertEqualsCanonicalizing(
            [$published->id],
            Post::query()->visibleTo(null)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$published->id, $ownedDraft->id],
            Post::query()->visibleTo($owner)->pluck('id')->all(),
        );
        $this->assertEqualsCanonicalizing(
            [$published->id, $ownedDraft->id, $otherDraft->id],
            Post::query()->visibleTo(User::factory()->moderator()->create())->pluck('id')->all(),
        );
    }

    public function test_publishing_and_unpublishing_manage_the_publication_timestamp(): void
    {
        $post = Post::factory()->draft()->create();

        $this->assertNull($post->published_at);

        $post->update(['status' => PostStatus::Published]);
        $this->assertNotNull($post->fresh()->published_at);

        $post->update(['status' => PostStatus::Draft]);
        $this->assertNull($post->fresh()->published_at);
    }

    public function test_deleting_a_user_cascades_to_their_posts(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $user->delete();

        $this->assertModelMissing($post);
    }
}
