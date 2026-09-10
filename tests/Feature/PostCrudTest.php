<?php

namespace Tests\Feature;

use App\Enums\ClaimStage;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_published_posts_but_not_drafts(): void
    {
        $published = Post::factory()->create(['title' => 'Published assessment experience']);
        $draft = Post::factory()->draft()->create(['title' => 'Private draft experience']);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSeeText($published->title)
            ->assertDontSeeText($draft->title);

        $this->get(route('posts.show', $published))
            ->assertOk()
            ->assertSeeText($published->title);

        $this->get(route('posts.show', $draft))->assertNotFound();
    }

    public function test_an_owner_can_view_their_draft(): void
    {
        $user = User::factory()->create();
        $draft = Post::factory()->draft()->for($user)->create();

        $this->actingAs($user)
            ->get(route('posts.show', $draft))
            ->assertOk()
            ->assertSeeText('Draft preview');
    }

    public function test_post_mutations_require_authentication(): void
    {
        $post = Post::factory()->create();

        $this->get(route('posts.create'))->assertRedirect(route('login'));
        $this->post(route('posts.store'))->assertRedirect(route('login'));
        $this->get(route('posts.edit', $post))->assertRedirect(route('login'));
        $this->put(route('posts.update', $post))->assertRedirect(route('login'));
        $this->delete(route('posts.destroy', $post))->assertRedirect(route('login'));
    }

    public function test_an_authenticated_user_can_create_a_published_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('posts.store'), $this->validPostData());

        $post = Post::query()->sole();

        $response->assertRedirect(route('posts.show', $post));
        $this->assertTrue($post->user->is($user));
        $this->assertSame(PostStatus::Published, $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertStringStartsWith('a-clear-assessment-experience-', $post->slug);
    }

    public function test_post_input_is_validated_and_sensitive_identifiers_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.store'), [
            'title' => '',
            'content' => 'Claim number CLM-123456 should never be public.',
            'claim_stage' => 'unknown-stage',
            'status' => 'unknown-status',
        ])->assertSessionHasErrors(['title', 'content', 'claim_stage', 'status']);

        $this->assertDatabaseEmpty('posts');
    }

    public function test_posts_with_the_same_title_receive_unique_server_generated_slugs(): void
    {
        $user = User::factory()->create();

        $first = $user->posts()->create($this->validPostData());
        $second = $user->posts()->create($this->validPostData());

        $this->assertNotSame($first->slug, $second->slug);
    }

    public function test_an_owner_can_update_and_unpublish_a_post_without_changing_its_slug(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $originalSlug = $post->slug;

        $this->actingAs($user)->put(route('posts.update', $post), [
            ...$this->validPostData(),
            'title' => 'A revised review experience',
            'status' => PostStatus::Draft->value,
        ])->assertRedirect(route('posts.show', $post));

        $post->refresh();

        $this->assertSame('A revised review experience', $post->title);
        $this->assertSame($originalSlug, $post->slug);
        $this->assertSame(PostStatus::Draft, $post->status);
        $this->assertNull($post->published_at);
    }

    public function test_a_regular_user_cannot_modify_another_users_post(): void
    {
        $post = Post::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->get(route('posts.edit', $post))->assertForbidden();
        $this->put(route('posts.update', $post), $this->validPostData())->assertForbidden();
        $this->delete(route('posts.destroy', $post))->assertForbidden();

        $this->assertModelExists($post);
    }

    public function test_moderators_can_update_posts_owned_by_other_users(): void
    {
        $post = Post::factory()->create();
        $moderator = User::factory()->moderator()->create();

        $this->actingAs($moderator)->put(route('posts.update', $post), [
            ...$this->validPostData(),
            'title' => 'Moderator-reviewed title',
        ])->assertRedirect(route('posts.show', $post));

        $this->assertSame('Moderator-reviewed title', $post->fresh()->title);
    }

    public function test_owners_and_administrators_can_delete_posts(): void
    {
        $owner = User::factory()->create();
        $ownersPost = Post::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->delete(route('posts.destroy', $ownersPost))
            ->assertRedirect(route('posts.index'));

        $this->assertModelMissing($ownersPost);

        $anotherPost = Post::factory()->create();
        $administrator = User::factory()->administrator()->create();

        $this->actingAs($administrator)
            ->delete(route('posts.destroy', $anotherPost))
            ->assertRedirect(route('posts.index'));

        $this->assertModelMissing($anotherPost);
    }

    public function test_user_content_is_escaped_when_a_post_is_displayed(): void
    {
        $post = Post::factory()->create([
            'content' => '<script>alert("unsafe")</script> This content remains safely escaped for every visitor.',
        ]);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertDontSee('<script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    /**
     * @return array{title: string, content: string, claim_stage: string, status: string}
     */
    private function validPostData(): array
    {
        return [
            'title' => 'A clear assessment experience',
            'content' => 'This claim experience explains the process clearly without including identifying information.',
            'claim_stage' => ClaimStage::Assessment->value,
            'status' => PostStatus::Published->value,
        ];
    }
}
