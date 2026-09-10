<?php

namespace Tests\Feature;

use App\Enums\ClaimStage;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PostSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_post_titles_and_content(): void
    {
        $titleMatch = Post::factory()->create(['title' => 'Assessment preparation checklist']);
        $contentMatch = Post::factory()->create([
            'title' => 'Preparing a clear timeline',
            'content' => 'The assessment conversation was easier after preparing general questions.',
        ]);
        $nonMatch = Post::factory()->create(['title' => 'Understanding a payment decision']);

        $this->get(route('posts.index', ['q' => 'assessment']))
            ->assertOk()
            ->assertSeeText($titleMatch->title)
            ->assertSeeText($contentMatch->title)
            ->assertDontSeeText($nonMatch->title);
    }

    public function test_posts_can_be_filtered_by_claim_stage(): void
    {
        $assessment = Post::factory()->create([
            'title' => 'Assessment stage experience',
            'claim_stage' => ClaimStage::Assessment,
        ]);
        $review = Post::factory()->create([
            'title' => 'Review stage experience',
            'claim_stage' => ClaimStage::Review,
        ]);

        $this->get(route('posts.index', ['claim_stage' => ClaimStage::Assessment->value]))
            ->assertOk()
            ->assertSeeText($assessment->title)
            ->assertDontSeeText($review->title);
    }

    public function test_status_filter_never_bypasses_post_visibility(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $ownedDraft = Post::factory()->draft()->for($owner)->create(['title' => 'Owned private draft']);
        $otherDraft = Post::factory()->draft()->for($otherOwner)->create(['title' => 'Another private draft']);

        $draftFilter = ['status' => PostStatus::Draft->value];

        $this->get(route('posts.index', $draftFilter))
            ->assertOk()
            ->assertDontSeeText($ownedDraft->title)
            ->assertDontSeeText($otherDraft->title);

        $this->actingAs($owner)
            ->get(route('posts.index', $draftFilter))
            ->assertSeeText($ownedDraft->title)
            ->assertDontSeeText($otherDraft->title);

        $this->actingAs(User::factory()->moderator()->create())
            ->get(route('posts.index', $draftFilter))
            ->assertSeeText($ownedDraft->title)
            ->assertSeeText($otherDraft->title);
    }

    public function test_posts_can_be_filtered_by_author(): void
    {
        $selectedAuthor = User::factory()->create(['name' => 'Selected Author']);
        $otherAuthor = User::factory()->create(['name' => 'Other Author']);
        $selectedPost = Post::factory()->for($selectedAuthor)->create(['title' => 'Selected author post']);
        $otherPost = Post::factory()->for($otherAuthor)->create(['title' => 'Other author post']);

        $this->get(route('posts.index', ['author' => $selectedAuthor->getKey()]))
            ->assertOk()
            ->assertSeeText($selectedPost->title)
            ->assertDontSeeText($otherPost->title);
    }

    public function test_discussion_filter_uses_active_comments_only(): void
    {
        $activeDiscussion = Post::factory()->create(['title' => 'Post with an active discussion']);
        $hiddenOnly = Post::factory()->create(['title' => 'Post with only hidden comments']);
        $withoutDiscussion = Post::factory()->create(['title' => 'Post without a discussion']);
        Comment::factory()->for($activeDiscussion)->create();
        Comment::factory()->hidden()->for($hiddenOnly)->create();

        $this->get(route('posts.index', ['discussion' => 'with_comments']))
            ->assertOk()
            ->assertSeeText($activeDiscussion->title)
            ->assertDontSeeText($hiddenOnly->title)
            ->assertDontSeeText($withoutDiscussion->title);

        $this->get(route('posts.index', ['discussion' => 'without_comments']))
            ->assertOk()
            ->assertDontSeeText($activeDiscussion->title)
            ->assertSeeText($hiddenOnly->title)
            ->assertSeeText($withoutDiscussion->title);
    }

    public function test_filters_are_combined_with_database_and_logic(): void
    {
        $author = User::factory()->create();
        $match = Post::factory()->for($author)->create([
            'title' => 'Privacy during assessment',
            'claim_stage' => ClaimStage::Assessment,
        ]);
        $wrongStage = Post::factory()->for($author)->create([
            'title' => 'Privacy during review',
            'claim_stage' => ClaimStage::Review,
        ]);
        $wrongAuthor = Post::factory()->create([
            'title' => 'Privacy during assessment for another author',
            'claim_stage' => ClaimStage::Assessment,
        ]);
        Comment::factory()->for($match)->create();
        Comment::factory()->for($wrongStage)->create();
        Comment::factory()->for($wrongAuthor)->create();

        $this->get(route('posts.index', [
            'q' => 'privacy',
            'claim_stage' => ClaimStage::Assessment->value,
            'author' => $author->getKey(),
            'discussion' => 'with_comments',
        ]))
            ->assertOk()
            ->assertSeeText($match->title)
            ->assertDontSeeText($wrongStage->title)
            ->assertDontSeeText($wrongAuthor->title);
    }

    public function test_invalid_filter_values_are_rejected(): void
    {
        $this->get(route('posts.index', [
            'q' => str_repeat('a', 101),
            'claim_stage' => 'invalid-stage',
            'status' => 'invalid-status',
            'author' => 'not-an-id',
            'discussion' => 'everything',
        ]))->assertSessionHasErrors(['q', 'claim_stage', 'status', 'author', 'discussion']);
    }

    public function test_pagination_preserves_active_filters(): void
    {
        Post::factory()->count(11)->create([
            'title' => 'Searchable assessment experience',
            'claim_stage' => ClaimStage::Assessment,
        ]);

        $this->get(route('posts.index', [
            'q' => 'searchable',
            'claim_stage' => ClaimStage::Assessment->value,
        ]))
            ->assertOk()
            ->assertViewHas('posts', function ($posts): bool {
                parse_str((string) parse_url($posts->nextPageUrl(), PHP_URL_QUERY), $query);

                return $posts->count() === 9
                    && ($query['q'] ?? null) === 'searchable'
                    && ($query['claim_stage'] ?? null) === ClaimStage::Assessment->value
                    && ($query['page'] ?? null) === '2';
            });
    }

    public function test_author_options_do_not_reveal_draft_only_authors(): void
    {
        $publishedAuthor = User::factory()->create();
        $draftOnlyAuthor = User::factory()->create();
        Post::factory()->for($publishedAuthor)->create();
        Post::factory()->draft()->for($draftOnlyAuthor)->create();

        $this->get(route('posts.index'))
            ->assertViewHas('authors', fn ($authors) => $authors->modelKeys() === [$publishedAuthor->getKey()]);
    }

    public function test_post_listing_uses_a_bounded_number_of_queries(): void
    {
        $posts = Post::factory()->count(9)->create();

        foreach ($posts as $post) {
            Comment::factory()->for($post)->create();
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get(route('posts.index'))->assertOk();

        $selectQueries = collect(DB::getQueryLog())
            ->filter(fn (array $query) => str_starts_with(strtolower(ltrim($query['query'])), 'select'));

        DB::disableQueryLog();

        $this->assertLessThanOrEqual(4, $selectQueries->count());
    }

    public function test_lazy_loading_is_prevented_during_tests(): void
    {
        $this->assertTrue(Model::preventsLazyLoading());
    }
}
