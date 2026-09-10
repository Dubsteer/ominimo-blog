<?php

namespace App\Policies;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

class CommentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        $moderationAbilities = ['view', 'delete', 'moderate'];

        return $user->canModerateContent() && in_array($ability, $moderationAbilities, true)
            ? true
            : null;
    }

    public function view(?User $user, Comment $comment): bool
    {
        return $comment->status === CommentStatus::Active
            || $comment->user_id === $user?->getKey()
            || ($user !== null && $this->postIsOwnedBy($user, $comment));
    }

    public function create(?User $user, Post $post): bool
    {
        return $post->status === PostStatus::Published;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->getKey() || $this->postIsOwnedBy($user, $comment);
    }

    public function moderate(User $user, Comment $comment): bool
    {
        return $user->canModerateContent();
    }

    private function postIsOwnedBy(User $user, Comment $comment): bool
    {
        if ($comment->relationLoaded('post')) {
            return $comment->post->user_id === $user->getKey();
        }

        return $comment->post()->where('user_id', $user->getKey())->exists();
    }
}
