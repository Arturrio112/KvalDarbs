<?php

namespace App\Http\Requests;

use App\Models\Follow;
use Illuminate\Foundation\Http\FormRequest;

class AddFollowRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai abi id nav vienādi un vai jau netiek sekots šim lietotājam
     */
    public function authorize(): bool
    {
        $follower_id = intval($this->input('follower_id'));
        $followed_id = intval($this->input('followed_id'));
        $existingFollow = Follow::where('follower_id', $follower_id)
            ->where('followed_id', $followed_id)
            ->exists();
        if(($followed_id == $follower_id)||$existingFollow){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Definēti pieprasījuma datu noteikumi
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'follower_id' => 'required|exists:user,id',
            'followed_id' => 'required|exists:user,id'
        ];
    }
}
