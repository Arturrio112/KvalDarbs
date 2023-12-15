<?php

namespace App\Http\Requests;

use App\Models\UserConversation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeleteConvoRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu
     * Pārbauda vai autentifikācijas talona lietotāja id ir vienāds ar padoto id'
     * Pārbauda vai dzēšamā saruna eksistē
     */
    public function authorize(): bool
    {
        $userId = $this->input('user_id');
        $convoId = $this->input('convo_id');
        $authenticatedUserId = Auth::id();
        if($authenticatedUserId != $userId){
            return false;
        }
        return UserConversation::where('conversation_id', $convoId)
        ->where('user_id', $userId)
        ->exists();;
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
            'convo_id' => 'required|exists:convo,id'
        ];
    }
}
