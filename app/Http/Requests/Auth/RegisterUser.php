<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class RegisterUser extends FormRequest
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
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            // 'password' => 'required|string|min:6',
            'password' => 'required|string|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{6,}$/',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Pole nazwy jest wymagane.',
            'name.string' => 'Nazwa musi być ciągiem znaków.',
            'name.max' => 'Nazwa nie może przekraczać 255 znaków.',

            'email.required' => 'Pole adresu e-mail jest wymagane.',
            'email.email' => 'Podany adres e-mail nie jest prawidłowy.',
            'email.unique' => 'Podany adres e-mail jest już używany.',

            'password.required' => 'Pole hasła jest wymagane.',
            'password.string' => 'Hasło musi być ciągiem znaków.',
            'password.min' => 'Hasło musi mieć co najmniej 6 znaków.',
            'password.regex' => 'Hasło musi zawierać co najmniej jedną małą literę, jedną wielką literę, jedną cyfrę oraz jeden znak specjalny.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json(['errors' => $validator->errors()], 422);
        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}