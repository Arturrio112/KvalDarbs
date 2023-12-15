<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convo extends Model
{
    use HasFactory;
    //Norāda tabulas vārdu
    protected $table = 'convo';
    //Definē relācijas ar citām datu bāzes tabulām
    public function userConversation(){
        return $this->hasMany(UserConversation::class);
    }
    public function message(){
        return $this->hasMany(Message::class);
    }
}
