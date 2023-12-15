<?php

namespace App\Http\Requests;

use App\Models\UserConversation;
use Illuminate\Foundation\Http\FormRequest;

class PostNewMessageRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     */
    public function authorize(): bool
    {
        $userId = $this->input('user_id');
        $conversationId = $this->input('conversation_id');

        // Pārbauda vai eksistē saruna
        return UserConversation::where('user_id', $userId)
            ->where('conversation_id', $conversationId)
            ->exists();
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
            'conversation_id' => 'required|exists:convo,id',
            'text' => 'required|string',
        ];
    }
}
