<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convo extends Model
{
    use HasFactory;
    protected $table = 'convo';
    
    public function userConversation(){
        return $this->hasMany(UserConversation::class);
    }
    public function message(){
        return $this->hasMany(Message::class);
    }
}
