<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Follow;
class RemoveFollowRequest extends FormRequest
{
    /**
     * Pārbauda vai lietotājs var veikt šo pieprasījumu.
     * Pārbauda vai abi padotie id ir vienadi un vai tiek sekots šim lietotājam
     */
    public function authorize(): bool
    {
        $follower_id = intval($this->input('follower_id'));
        $followed_id = intval($this->input('followed_id'));
        $existingFollow = Follow::where('follower_id', $follower_id)
            ->where('followed_id', $followed_id)
            ->exists();
        if($followed_id == $follower_id||!$existingFollow){
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
