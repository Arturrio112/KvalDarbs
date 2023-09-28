<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
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
