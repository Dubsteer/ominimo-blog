<?php

namespace App\Http\Requests\Post;

use App\Enums\ClaimStage;
use App\Enums\PostStatus;
use App\Rules\SafePublicContent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

abstract class PostRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['bail', 'required', 'string', 'max:160', new SafePublicContent],
            'content' => ['bail', 'required', 'string', 'min:30', 'max:50000', new SafePublicContent],
            'claim_stage' => ['required', Rule::enum(ClaimStage::class)],
            'status' => ['required', Rule::enum(PostStatus::class)],
            'image' => [
                'nullable',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max('8mb')
                    ->dimensions(Rule::dimensions()->maxWidth(8000)->maxHeight(8000)),
            ],
            'remove_image' => [
                'sometimes',
                'boolean',
                Rule::prohibitedIf($this->hasFile('image')),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'content' => trim((string) $this->input('content')),
        ]);
    }
}
