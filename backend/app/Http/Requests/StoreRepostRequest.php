<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
class StoreRepostRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai autentifikācijas talona lietotāja id ir vienāds ar padoto id
     * Pārbauda vai raksta autora id ir vienāds ar ipriekš minētajiem id
     */
    public function authorize(): bool
    {   
        $authenticatedUserId = Auth::id();

        $userId = $this->input('user_id');
        $postId = $this->input('post_id');
        $post = Post::find($postId);

        return $post &&$userId == $authenticatedUserId && $authenticatedUserId != $post->user_id;
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
            'post_id' => 'required|exists:post,id'
        ];
    }
}
