<?php

namespace App\Http\Requests\Admin\UserManagement;

use App\Enums\RoleName;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'username')->where(function (Builder $query): void {
                    $query->where('delete_status', false);
                }),
            ],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(function (Builder $query): void {
                    $query
                        ->where('delete_status', false)
                        ->whereIn('name', [RoleName::Admin->value, RoleName::Hrd->value, RoleName::Employee->value]);
                }),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'name' => trim((string) $this->input('name')),
            'username' => trim((string) $this->input('username')),
            'email' => strtolower(trim((string) $this->input('email'))),
            'role_id' => (int) $this->input('role_id'),
            'password' => (string) $this->input('password'),
            'profile_image' => $this->file('profile_image'),
        ];
    }
}
