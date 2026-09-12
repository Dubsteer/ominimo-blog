<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class SearchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (array_keys($this->rules()) as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $value = trim($this->input($key));
                $normalized[$key] = $value === '' ? null : $value;
            }
        }

        $this->merge($normalized);
    }
}
