<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInternalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:3'],
            'is_important' => ['nullable', 'boolean'],
            'attachments' => ['sometimes', 'array'],
            'attachments.*' => ['file', 'max:5120'],
        ];
    }
}
