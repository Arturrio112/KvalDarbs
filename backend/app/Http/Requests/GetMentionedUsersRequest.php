<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetMentionedUsersRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'usernames' => 'required|string',
        ];
    }
}
