<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class DeleteCommentRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai autentifikācijas talona lietotāja id ir vienāds ar padoto id
     * Pārbauda vai komentārs, kuru vēlas dzēst eksistē
     */
    public function authorize(): bool
    {
        
        $user_id = $this->input('user_id');
        $comment_id = $this->input('comment_id');
        $authenticatedUserId = Auth::id();
        if($authenticatedUserId !=$user_id){
            return false;
        }
        return Comment::where('id', $comment_id)
            ->where('user_id', $user_id)
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
            'comment_id' => 'required|exists:comment,id'
        ];
    }
}
