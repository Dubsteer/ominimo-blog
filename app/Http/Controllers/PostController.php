<?php

namespace App\Http\Controllers;

use App\Enums\ClaimStage;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Http\Requests\Post\SearchPostsRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(SearchPostsRequest $request): View
    {
        $filters = $request->validated();
        $activeComments = fn (Builder $comments) => $comments->where('status', CommentStatus::Active->value);

        $posts = Post::query()
            ->visibleTo($request->user())
            ->search($filters['q'] ?? null)
            ->when(
                $filters['claim_stage'] ?? null,
                fn (Builder $query, string $claimStage) => $query->where('claim_stage', $claimStage),
            )
            ->when(
                $filters['status'] ?? null,
                fn (Builder $query, string $status) => $query->where('status', $status),
            )
            ->when(
                $filters['author'] ?? null,
                fn (Builder $query, int|string $author) => $query->where('user_id', $author),
            )
            ->when($filters['discussion'] ?? null, function (Builder $query, string $discussion) use ($activeComments): void {
                if ($discussion === 'with_comments') {
                    $query->whereHas('comments', $activeComments);

                    return;
                }

                $query->whereDoesntHave('comments', $activeComments);
            })
            ->with('user:id,name')
            ->withCount([
                'comments as comments_count' => $activeComments,
            ])
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(9)
            ->withQueryString();

        $authors = User::query()
            ->whereHas('posts', fn (Builder $query) => $query->visibleTo($request->user()))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('posts.index', [
            'posts' => $posts,
            'filters' => $filters,
            'claimStages' => ClaimStage::cases(),
            'statuses' => PostStatus::cases(),
            'authors' => $authors,
            'discussionOptions' => [
                'with_comments' => 'With comments',
                'without_comments' => 'Without comments',
            ],
            'hasFilters' => collect($filters)->contains(fn (mixed $value) => filled($value)),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Post::class);

        return view('posts.create', $this->formOptions());
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'post-created');
    }

    public function show(Request $request, Post $post): View
    {
        Gate::authorize('view', $post);
        $post->loadMissing('user:id,name');
        $comments = $post->comments()
            ->visibleTo($request->user())
            ->with('user:id,name')
            ->oldest()
            ->get();

        // Every result belongs to the route-bound post, so reuse it for policy checks.
        $comments->each(fn (Comment $comment) => $comment->setRelation('post', $post));

        return view('posts.show', compact('post', 'comments'));
    }

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('posts.edit', [
            'post' => $post,
            ...$this->formOptions(),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'post-updated');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('status', 'post-deleted');
    }

    /**
     * @return array{claimStages: array<int, ClaimStage>, statuses: array<int, PostStatus>}
     */
    private function formOptions(): array
    {
        return [
            'claimStages' => ClaimStage::cases(),
            'statuses' => PostStatus::cases(),
        ];
    }
}
