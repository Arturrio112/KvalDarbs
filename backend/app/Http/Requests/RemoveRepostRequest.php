<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RemoveRepostRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai autentifikācijas talona lietotāja id ir vienāds ar padoto id
     */
    public function authorize(): bool
    {
        $authenticatedUser = Auth::user();
        return $authenticatedUser && $authenticatedUser->id == $this->input('user_id');
    }

    /**
     * Definēti pieprasījuma datu noteikumi
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:user,id',
            'post_id' => 'required|exists:post,id'
        ];
    }
}
