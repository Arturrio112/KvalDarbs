<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Definēti pieprasījuma datu noteikumi
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'=>['required', 'string', 'max:255', 'unique:user'],
            'email'=>['required', 'string', 'max:255', 'unique:user'],
            'password'=>['required', 'confirmed', Rules\Password::defaults()],
            'birthdate' => ['required', 'date', 'before:-13 years']
        ];
    }
}
