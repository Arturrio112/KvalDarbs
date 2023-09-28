<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Profile;
class DeleteUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get the profile ID from the route parameters
            $profileId = (int) $this->route('id'); // Assumes the profile ID is in the route parameters

            // Retrieve the profile using the ID
            $profile = Profile::find($profileId);

            // Check if the profile exists and the authenticated user's profile ID matches the requested profile's user_id
            return $profile && $this->user()->id === $profile->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
