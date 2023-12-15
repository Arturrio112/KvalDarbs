<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    //Norāda datu bāzes tabulas nosaukumu un aizpildāmos laukus
    protected $table = 'message';
    protected $fillable =[
        'user_id',
        'conversation_id',
        'text'
    ];
    //Definē relācijas ar citām datu bāzes tabulām
    public function convo(){
        return $this->belongsTo(Message::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
