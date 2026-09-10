<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentStatusRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        $comment = new Comment($request->validated());
        $comment->user()->associate($request->user());
        $post->comments()->save($comment);

        return redirect()
            ->to(route('posts.show', $post).'#comment-'.$comment->getKey())
            ->with('status', 'comment-created');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);
        $post = $comment->post()->firstOrFail();
        $comment->delete();

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'comment-deleted');
    }

    public function updateStatus(UpdateCommentStatusRequest $request, Comment $comment): RedirectResponse
    {
        $post = $comment->post()->firstOrFail();
        $comment->update($request->validated());

        return redirect()
            ->to(route('posts.show', $post).'#comment-'.$comment->getKey())
            ->with('status', 'comment-status-updated');
    }
}
