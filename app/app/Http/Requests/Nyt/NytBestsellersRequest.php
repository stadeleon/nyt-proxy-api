<?php

namespace App\Http\Requests\Nyt;

use Illuminate\Foundation\Http\FormRequest;

class NytBestsellersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author' => 'nullable|string|max:255',
            'title'  => 'nullable|string|max:255',
            'isbn'   => 'nullable|string|max:20',
            'offset' => 'nullable|integer|min:0',
        ];
    }
}
