<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowOneUserRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
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
            'user_id' => 'required|exists:user,id'
        ];
    }
}
