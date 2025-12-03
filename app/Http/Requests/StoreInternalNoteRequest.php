<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_code' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'min:3'],
        ];
    }
}
