<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow guests to submit this form
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-zĀ-žā-ž\s]+$/u'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[A-Za-z0-9_.]+$/', 'unique:users,username'],
            'email' => ['required', 'string', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'role' => ['required', 'in:reguser,provider'],
        ];
    }

    /**
     * Field-specific messages (for most rules).
     * NOTE: Password complexity messages are best customized in lang/validation.php (see step 4).
     */
    public function messages(): array
    {
        return [
            'password.required' => 'Please enter a password.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 8 characters, include uppercase, lowercase letters, one number and one special character.',
            'password.mixed' => 'Password must be at least 8 characters, include uppercase, lowercase letters, one number and one special character.',
            'password.numbers' => 'Password must be at least 8 characters, include uppercase, lowercase letters, one number and one special character.',
            'password.symbols' => 'Password must be at least 8 characters, include uppercase, lowercase letters, one number and one special character.',
        ];
    }
}
