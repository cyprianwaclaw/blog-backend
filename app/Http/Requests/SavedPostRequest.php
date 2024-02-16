<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class SavedPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'post_id' => 'required|integer|exists:App\Models\Post,id',
        ];
    }
    protected function prepareForValidation()
    {
        // Dodaj 'user_id' do danych przed walidacją
        $this->merge([
            'user_id' => auth()->user()->id,
        ]);
    }
    /**
     * Manipulacja walidatorem po standardowej walidacji.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json(['errors' => $validator->errors()], 422);
        throw new ValidationException($validator, $response);
    }
}
