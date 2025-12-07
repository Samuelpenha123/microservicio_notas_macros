<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMacroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string'],
            'scope' => ['required', Rule::in(['personal', 'global'])],
            'category' => ['nullable', 'string', 'max:120'],
        ];
    }
}
