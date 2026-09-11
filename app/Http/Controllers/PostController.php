<?php

namespace App\Http\Controllers;

use App\Enums\ClaimStage;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Http\Requests\Post\SearchPostsRequest;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Jobs\ProcessPostImage;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\PostImageStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

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

    public function store(StorePostRequest $request, PostImageStorage $images): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $originalPath = null;

        try {
            if ($request->hasFile('image')) {
                $originalPath = $images->storeOriginal($request->file('image'));
            }

            $post = DB::transaction(function () use ($request, $data, $originalPath): Post {
                $post = $request->user()->posts()->make($data);
                $post->original_image_path = $originalPath;
                $post->save();

                if ($originalPath !== null) {
                    ProcessPostImage::dispatch($post->getKey(), $originalPath);
                }

                return $post;
            });
        } catch (Throwable $exception) {
            $images->delete($originalPath, null);

            throw $exception;
        }

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

    public function update(UpdatePostRequest $request, Post $post, PostImageStorage $images): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $oldOriginalPath = $post->original_image_path;
        $oldProcessedPath = $post->processed_image_path;
        $newOriginalPath = null;
        $hasReplacement = $request->hasFile('image');
        $removeImage = $request->boolean('remove_image');

        try {
            if ($hasReplacement) {
                $newOriginalPath = $images->storeOriginal($request->file('image'));
            }

            DB::transaction(function () use (
                $post,
                $data,
                $hasReplacement,
                $removeImage,
                $newOriginalPath,
            ): void {
                $post->fill($data);

                if ($hasReplacement) {
                    $post->original_image_path = $newOriginalPath;
                    $post->processed_image_path = null;
                } elseif ($removeImage) {
                    $post->original_image_path = null;
                    $post->processed_image_path = null;
                }

                $post->save();

                if ($newOriginalPath !== null) {
                    ProcessPostImage::dispatch($post->getKey(), $newOriginalPath);
                }
            });
        } catch (Throwable $exception) {
            $images->delete($newOriginalPath, null);

            throw $exception;
        }

        if ($hasReplacement || $removeImage) {
            $images->delete($oldOriginalPath, $oldProcessedPath);
        }

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'post-updated');
    }

    public function destroy(Post $post, PostImageStorage $images): RedirectResponse
    {
        Gate::authorize('delete', $post);
        $originalPath = $post->original_image_path;
        $processedPath = $post->processed_image_path;
        $post->delete();
        $images->delete($originalPath, $processedPath);

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
