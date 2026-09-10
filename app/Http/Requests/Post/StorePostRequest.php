<?php

namespace App\Http\Requests\Post;

use App\Models\Post;

class StorePostRequest extends PostRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Post::class) ?? false;
    }
}
