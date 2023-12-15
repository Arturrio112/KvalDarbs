<?php

namespace App\Http\Requests;

use App\Models\UserConversation;
use Illuminate\Foundation\Http\FormRequest;

class StartNewConvoRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     */
    public function authorize(): bool
    {
        $user1_id = $this->input('user1_id');
        $user2_id = $this->input('user2_id');
        
        
        // Pārbauda vai saruna starp šiem diviem eksistē 
        $conversationExists = UserConversation::select('conversation_id') 
            ->where(function ($query) use ($user1_id, $user2_id) {
                $query->where('user_id', $user1_id)
                    ->orWhere('user_id', $user2_id);
            })
            ->groupBy('conversation_id') 
            ->havingRaw('COUNT(DISTINCT user_id) = 2')
            ->exists();

        // Atgriež false ja eksistē
        return !$conversationExists;
    }

    /**
     * Definēti pieprasījuma datu noteikumi
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user1_id' => 'required|exists:user,id|different:user2_id',
            'user2_id' => 'required|exists:user,id|different:user1_id',
            'message' => 'required|string',
        ];
    }
}
