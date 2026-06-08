<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function email(): string
    {
        /** @var string $email */
        $email = $this->input('email');

        return $email;
    }

    public function password(): string
    {
        /** @var string $password */
        $password = $this->input('password');

        return $password;
    }

    public function remember(): bool
    {
        return $this->boolean('remember');
    }
}
