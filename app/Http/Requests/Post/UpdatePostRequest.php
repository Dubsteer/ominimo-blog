<?php

namespace App\Http\Requests\Post;

use App\Models\Post;

class UpdatePostRequest extends PostRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && ($this->user()?->can('update', $post) ?? false);
    }
}
