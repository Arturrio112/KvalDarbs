<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConversation extends Model
{
    use HasFactory;
    //Norāda datu bāzes tabulas nosaukumu un aizpildāmos laukus
    protected $table = 'user_conversation';
    protected $fillable =[
        'user_id',
        'conversation_id'
    ];
    //Definē relācijas ar citām datu bāzes tabulām
    public function convo(){
        return $this->belongsTo(Message::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
