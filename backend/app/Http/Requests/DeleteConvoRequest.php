<?php

namespace App\Http\Requests;

use App\Models\UserConversation;
use Illuminate\Foundation\Http\FormRequest;

class DeleteConvoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $userId = $this->input('user_id');
        $convoId = $this->input('convo_id');
        return UserConversation::where('conversation_id', $convoId)
        ->where('user_id', $userId)
        ->exists();;
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
            'convo_id' => 'required|exists:convo,id'
        ];
    }
}
