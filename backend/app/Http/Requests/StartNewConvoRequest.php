<?php

namespace App\Http\Requests;

use App\Models\UserConversation;
use Illuminate\Foundation\Http\FormRequest;

class StartNewConvoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user1_id = $this->input('user1_id');
        $user2_id = $this->input('user2_id');
        
        // Check if a conversation already exists between the two users
        $conversationExists = UserConversation::select('conversation_id') // Select only the necessary column
            ->where(function ($query) use ($user1_id, $user2_id) {
                $query->where('user_id', $user1_id)
                    ->orWhere('user_id', $user2_id);
            })
            ->groupBy('conversation_id') // Group by conversation_id
            ->havingRaw('COUNT(DISTINCT user_id) = 2')
            ->exists();

        // Return false if conversation already exists, true otherwise
        return !$conversationExists;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user1_id' => 'required|different:user2_id',
            'user2_id' => 'required|different:user1_id',
            'message' => 'required|string',
        ];
    }
}
