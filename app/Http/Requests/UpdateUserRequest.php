<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $id = request()->route('id');

        return [
            'username' => "sometimes|string|min:3|max:20|unique:users,username,$id",
            'email'    => "sometimes|email|unique:users,email,$id",
            'password' => 'sometimes|string|min:8',
            'role'     => 'sometimes|in:admin,seller,customer',
            'gender'   => 'nullable|in:male,female,other',
        ];
    }
}
