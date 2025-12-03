<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:3'],
            'internal' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'internal' => $this->boolean('internal'),
        ]);
    }
}
