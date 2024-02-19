<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class UpdateCommentRequest extends FormRequest
{

    public function rules(): array
    {

        return [
            'comment_id' => 'required|integer|exists:App\Models\Comment,id',
            'text' => 'required|string',
            'relaction' => [
                'nullable',
                // Rule::in(['null', 'like', 'heart', 'haha']),
            ],
        ];
    }
    // $table->enum('relaction', ['null', 'like', 'heart', 'haha'])->nullable();

    public function messages()
    {
        return [
            'comment_id.required' => 'Pole "Id komentarza" jest wymagane',
            'comment_id.integer' => 'Pole "Id komentarza" musi być liczbą całkowitą',
            'comment_id.exists' => 'Podany "Id komentarza" nie istnieje',
            'text.required' => 'Pole "Tekst" jest wymagane',
            // 'relaction.in' => "Relacja musi być puste lub mieć wartość 'heart'",
        ];
    }
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->user()->id,
        ]);
    }
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json(['errors' => $validator->errors()], 422);
        throw new ValidationException($validator, $response);
    }
}