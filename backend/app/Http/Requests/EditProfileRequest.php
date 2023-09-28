<?php

namespace App\Http\Requests;

use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;

class EditProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {   
        $profile = Profile::find($this->input('profileId'));
        // Only allow the request if userId is the same as profileId
        return intval($this->input('userId')) === $profile->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'profileName' => 'required|string',
            'profilePicture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Assuming a maximum file size of 2MB
            'profileId' => 'required|exists:profile,id',
            'userId' => 'required|exists:user,id',
        ];
    }

}

