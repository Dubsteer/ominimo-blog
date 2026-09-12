<?php

namespace Tests\Feature\Admin;

use App\Enums\ClaimStage;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_moderation_dashboard_enforces_role_boundaries(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.index'))
            ->assertForbidden();

        $this->actingAs(User::factory()->moderator()->create())
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSeeText('Moderation workspace');

        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSeeText('Manage roles');
    }

    public function test_dashboard_filters_posts_for_moderation(): void
    {
        $moderator = User::factory()->moderator()->create();
        $author = User::factory()->create();
        $match = Post::factory()->draft()->for($author)->create([
            'title' => 'Assessment document review',
            'claim_stage' => ClaimStage::Assessment,
        ]);
        $other = Post::factory()->create([
            'title' => 'Closed claim experience',
            'claim_stage' => ClaimStage::Closed,
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.index', [
                'post_q' => ' document ',
                'post_status' => ' draft ',
                'post_claim_stage' => ' assessment ',
                'post_author' => $author->getKey(),
            ]))
            ->assertOk()
            ->assertViewHas('filters', [
                'post_q' => 'document',
                'post_status' => PostStatus::Draft->value,
                'post_claim_stage' => ClaimStage::Assessment->value,
                'post_author' => (string) $author->getKey(),
            ])
            ->assertSeeText($match->title)
            ->assertDontSeeText($other->title);
    }

    public function test_dashboard_filters_comments_for_moderation(): void
    {
        $moderator = User::factory()->moderator()->create();
        $memberComment = Comment::factory()->hidden()->create([
            'comment' => 'This private detail should be reviewed.',
        ]);
        $guestComment = Comment::factory()->guest()->create([
            'comment' => 'A general public question.',
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.index', [
                'comment_q' => 'private detail',
                'comment_status' => CommentStatus::Hidden->value,
                'comment_author' => $memberComment->user_id,
                'comment_origin' => 'member',
            ]))
            ->assertOk()
            ->assertSeeText($memberComment->comment)
            ->assertDontSeeText($guestComment->comment);
    }

    public function test_invalid_moderation_filters_are_rejected(): void
    {
        $moderator = User::factory()->moderator()->create();

        $this->actingAs($moderator)
            ->get(route('admin.index', [
                'post_status' => 'removed',
                'post_claim_stage' => 'unknown',
                'comment_status' => 'removed',
                'comment_origin' => 'anonymous',
            ]))
            ->assertSessionHasErrors([
                'post_status',
                'post_claim_stage',
                'comment_status',
                'comment_origin',
            ]);
    }

    public function test_moderators_and_administrators_can_change_post_status(): void
    {
        $post = Post::factory()->draft()->create();

        foreach ([
            User::factory()->moderator()->create(),
            User::factory()->administrator()->create(),
        ] as $reviewer) {
            $this->actingAs($reviewer)
                ->patch(route('admin.posts.status.update', $post), [
                    'status' => PostStatus::Published->value,
                ])
                ->assertRedirect(route('admin.index').'#posts');

            $this->assertSame(PostStatus::Published, $post->fresh()->status);
            $post->update(['status' => PostStatus::Draft]);
        }
    }

    public function test_regular_users_cannot_change_post_status(): void
    {
        $post = Post::factory()->draft()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.posts.status.update', $post), [
                'status' => PostStatus::Published->value,
            ])
            ->assertForbidden();

        $this->assertSame(PostStatus::Draft, $post->fresh()->status);
    }

    public function test_moderators_and_administrators_can_change_comment_status(): void
    {
        $comment = Comment::factory()->create();

        foreach ([
            User::factory()->moderator()->create(),
            User::factory()->administrator()->create(),
        ] as $reviewer) {
            $this->actingAs($reviewer)
                ->patch(route('admin.comments.status.update', $comment), [
                    'status' => CommentStatus::Hidden->value,
                ])
                ->assertRedirect(route('admin.index').'#comments');

            $this->assertSame(CommentStatus::Hidden, $comment->fresh()->status);
            $comment->update(['status' => CommentStatus::Active]);
        }
    }

    public function test_regular_users_cannot_change_comment_status(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.comments.status.update', $comment), [
                'status' => CommentStatus::Hidden->value,
            ])
            ->assertForbidden();

        $this->assertSame(CommentStatus::Active, $comment->fresh()->status);
    }

    public function test_invalid_status_changes_do_not_modify_content(): void
    {
        $moderator = User::factory()->moderator()->create();
        $post = Post::factory()->draft()->create();
        $comment = Comment::factory()->create();

        $this->actingAs($moderator)
            ->patch(route('admin.posts.status.update', $post), ['status' => 'removed'])
            ->assertSessionHasErrors('status');

        $this->actingAs($moderator)
            ->patch(route('admin.comments.status.update', $comment), ['status' => 'removed'])
            ->assertSessionHasErrors('status');

        $this->assertSame(PostStatus::Draft, $post->fresh()->status);
        $this->assertSame(CommentStatus::Active, $comment->fresh()->status);
    }

    public function test_post_and_comment_pagination_stay_independent_and_preserve_filters(): void
    {
        $moderator = User::factory()->moderator()->create();
        Post::factory()->draft()->count(9)->create();
        $post = Post::factory()->create();
        Comment::factory()->guest()->for($post)->count(9)->create();

        $response = $this->actingAs($moderator)
            ->get(route('admin.index', [
                'post_status' => PostStatus::Draft->value,
                'comment_origin' => 'guest',
                'posts_page' => 2,
                'comments_page' => 2,
            ]))
            ->assertOk();

        $posts = $response->viewData('posts');
        $comments = $response->viewData('comments');

        $this->assertSame(2, $posts->currentPage());
        $this->assertSame(1, $posts->count());
        $this->assertSame(2, $comments->currentPage());
        $this->assertSame(1, $comments->count());
        $this->assertStringContainsString('comment_origin=guest', $posts->url(1));
        $this->assertStringContainsString('post_status=draft', $comments->url(1));
    }
}
