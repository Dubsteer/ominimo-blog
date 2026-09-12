<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStage;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SearchModerationRequest;
use App\Http\Requests\Admin\UpdatePostStatusRequest;
use App\Http\Requests\Comment\UpdateCommentStatusRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function index(SearchModerationRequest $request): View
    {
        $filters = $request->validated();

        $posts = Post::query()
            ->search($filters['post_q'] ?? null)
            ->when(
                $filters['post_status'] ?? null,
                fn (Builder $query, string $status) => $query->where('status', $status),
            )
            ->when(
                $filters['post_claim_stage'] ?? null,
                fn (Builder $query, string $stage) => $query->where('claim_stage', $stage),
            )
            ->when(
                $filters['post_author'] ?? null,
                fn (Builder $query, int|string $author) => $query->where('user_id', $author),
            )
            ->with('user:id,name')
            ->withCount('comments')
            ->latest()
            ->paginate(8, ['*'], 'posts_page')
            ->withQueryString();

        $comments = Comment::query()
            ->when($filters['comment_q'] ?? null, function (Builder $query, string $term): void {
                $query->where('comment', 'like', '%'.$term.'%');
            })
            ->when(
                $filters['comment_status'] ?? null,
                fn (Builder $query, string $status) => $query->where('status', $status),
            )
            ->when(
                $filters['comment_author'] ?? null,
                fn (Builder $query, int|string $author) => $query->where('user_id', $author),
            )
            ->when($filters['comment_origin'] ?? null, function (Builder $query, string $origin): void {
                $origin === 'guest'
                    ? $query->whereNull('user_id')
                    : $query->whereNotNull('user_id');
            })
            ->with(['user:id,name', 'post:id,title,slug'])
            ->latest()
            ->paginate(8, ['*'], 'comments_page')
            ->withQueryString();

        return view('admin.index', [
            'posts' => $posts,
            'comments' => $comments,
            'filters' => $filters,
            'claimStages' => ClaimStage::cases(),
            'postStatuses' => PostStatus::cases(),
            'commentStatuses' => CommentStatus::cases(),
            'postAuthors' => User::query()
                ->whereHas('posts')
                ->orderBy('name')
                ->get(['id', 'name']),
            'commentAuthors' => User::query()
                ->whereHas('comments')
                ->orderBy('name')
                ->get(['id', 'name']),
            'stats' => [
                'posts' => Post::query()->count(),
                'drafts' => Post::query()->where('status', PostStatus::Draft->value)->count(),
                'comments' => Comment::query()->count(),
                'hiddenComments' => Comment::query()->where('status', CommentStatus::Hidden->value)->count(),
            ],
            'hasPostFilters' => collect($filters)->only([
                'post_q',
                'post_status',
                'post_claim_stage',
                'post_author',
            ])->contains(fn (mixed $value) => filled($value)),
            'hasCommentFilters' => collect($filters)->only([
                'comment_q',
                'comment_status',
                'comment_author',
                'comment_origin',
            ])->contains(fn (mixed $value) => filled($value)),
        ]);
    }

    public function updatePostStatus(UpdatePostStatusRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        return redirect()
            ->to(route('admin.index').'#posts')
            ->with('status', 'post-status-updated');
    }

    public function updateCommentStatus(UpdateCommentStatusRequest $request, Comment $comment): RedirectResponse
    {
        $comment->update($request->validated());

        return redirect()
            ->to(route('admin.index').'#comments')
            ->with('status', 'comment-status-updated');
    }
}
