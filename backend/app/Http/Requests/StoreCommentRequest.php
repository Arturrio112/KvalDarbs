<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class StoreCommentRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai autentifikācijas talona lietotāja id ir vienāds ar padoto id
     */
    public function authorize(): bool
    {
        $authenticatedUserId = Auth::id();
        $requestedUserId = $this->input('user_id');

        return $authenticatedUserId == $requestedUserId;
    }

    /**
     * Definēti pieprasījuma datu noteikumi
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'text' => 'required|string|max:255',
            'fileFormat' => 'nullable|string|max:255', 
            'media' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048', 
            'user_id' => 'required|exists:user,id',
            'post_id' => 'required|exists:post,id'
        ];
    }
}
