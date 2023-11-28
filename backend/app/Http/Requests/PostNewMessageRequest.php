<?php

namespace App\Http\Requests;

use App\Models\UserConversation;
use Illuminate\Foundation\Http\FormRequest;

class PostNewMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = $this->input('user_id');
        $conversationId = $this->input('conversation_id');

        // Check if there is a corresponding row in user_conversation
        return UserConversation::where('user_id', $userId)
            ->where('conversation_id', $conversationId)
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
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
