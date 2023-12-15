<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    //Norāda datu bāzes tabulas nosaukumu un aizpildāmos laukus
    protected $table = 'profile';

    protected $fillable =[
        'user_id',
        'nickname',
        'picture',
        'verified'
    ];
    //Definē relācijas ar citām datu bāzes tabulām
    public function user(){
        return $this->belongsTo(User::class);
    }
}
