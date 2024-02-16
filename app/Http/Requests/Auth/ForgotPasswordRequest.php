<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class ForgotPasswordRequest extends FormRequest
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
            'email' => 'required|email',
            'newPassword' => 'required|string',
            'confirmPassword' => 'required|string|same:newPassword',
        ];
    }
    public function messages(): array
    {
        return [
            'email.required' => 'Adres e-mail jest wymagany.',
            'email.email' => 'Nieprawidłowy format adresu e-mail.',
            'newPassword.required' => 'Hasło jest wymagane.',
            'newPassword.regex' => 'Hasło musi zawierać co najmniej jedną wielką literę, jedną cyfrę i jeden znak specjalny.',
            'confirmPassword.required' => 'Potwierdzenie hasła jest wymagane.',
            'confirmPassword.same' => 'Potwierdzenie hasła musi być identyczne z nowym hasłem.',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json(['errors' => $validator->errors()], 422);
        throw new ValidationException($validator, $response);
    }
}
