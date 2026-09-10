<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use App\Models\Post;
use App\Rules\SafePublicContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post
            && Gate::forUser($this->user())->allows('create', [Comment::class, $post]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'comment' => ['bail', 'required', 'string', 'min:3', 'max:2000', new SafePublicContent],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'comment' => trim((string) $this->input('comment')),
        ]);
    }
}
