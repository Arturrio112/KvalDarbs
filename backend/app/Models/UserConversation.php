<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConversation extends Model
{
    use HasFactory;
    protected $table = 'user_conversation';
    protected $fillable =[
        'user_id',
        'conversation_id'
    ];
    public function convo(){
        return $this->belongsTo(Message::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}