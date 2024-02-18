<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class CommentRequest extends FormRequest
{

    public function rules(): array
    {

        return [
            'post_id' => 'required|integer|exists:App\Models\Post,id',
            'text' => 'required|string',
        ];
    }
    protected function prepareForValidation()
    {
        // Dodaj 'user_id' do danych przed walidacją
        $this->merge([
            'user_id' => auth()->user()->id,
            'relaction' => null,
        ]);
    }
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json(['errors' => $validator->errors()], 422);
        throw new ValidationException($validator, $response);
    }
}
