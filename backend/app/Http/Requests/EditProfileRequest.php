<?php

namespace App\Http\Requests;

use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class EditProfileRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai autentifikācijas talona lietotāja id ir vienāds ar padoto id
     */
    public function authorize(): bool
    {   
        $profile = Profile::find($this->input('profileId'));
        $authenticatedUserId = Auth::id();
        return intval($this->input('userId')) === $profile->user_id && $authenticatedUserId == $profile->user_id;
    }

    /**
     * Definēti pieprasījuma datu noteikumi
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'profileName' => 'required|string',
            'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fontColor' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'], // Adjusted regex
            'borderColor' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'], // Adjusted regex
            'profileId' => 'required|exists:profile,id',
            'userId' => 'required|exists:user,id',
        ];
    }

}

