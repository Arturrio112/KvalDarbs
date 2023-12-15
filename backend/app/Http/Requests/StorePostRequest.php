<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class StorePostRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai autentifikācijas talona lietotāja id ir vienāds ar padoto id
     */
    public function authorize(): bool
    {
        $userId = $this->input('user_id');
        $authenticatedUserId = Auth::id();
        if($authenticatedUserId != $userId){
            return false;
        }else{
            return true;
        }
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
            'media' => 'nullable|file|mimes:jpeg,png,mp4,gif|max:2048', 
            'user_id' => 'required|exists:user,id',
        ];
    }
}
