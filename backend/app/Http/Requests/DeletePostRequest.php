<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DeletePostRequest extends FormRequest
{
   /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai autentifikācijas talona lietotāja id ir vienāds ar padoto id
     * Pārbauda vai raksta autora id ir vienada ar abiem iepriekš minētajiem id
     */
    public function authorize(): bool
    {
        $userId = $this->input('user_id');
        $post = Post::find($this->input('post_id'));
        $authenticatedUserId = Auth::id();
        
        return $post && $post->user_id === $userId && $post->user_id == $authenticatedUserId;
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
            'post_id' => 'required|exists:post,id'
        ];
    }
}
