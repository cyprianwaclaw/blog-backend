<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class CreatePostRequest extends FormRequest
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
            'name' => 'required|string|unique:posts,name',
            'link' => 'required|string|unique:posts,link',
            'description' => 'required|string',
            'hero-image' => 'required|image',
        ];
        // 'category_ids' => 'required|array'
    }
    protected function prepareForValidation()
    {
        // Dodaj 'user_id' do danych przed walidacją
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
