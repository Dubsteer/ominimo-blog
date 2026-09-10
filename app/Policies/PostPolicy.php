<?php

namespace App\Policies;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->canModerateContent() ? true : null;
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Post $post): Response
    {
        return $post->status === PostStatus::Published || $post->user_id === $user?->getKey()
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        return $post->user_id === $user->getKey();
    }

    public function delete(User $user, Post $post): bool
    {
        return $post->user_id === $user->getKey();
    }
}
