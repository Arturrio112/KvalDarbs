<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;
    //Norāda datu bāzes tabulas nosaukumu un aizpildāmos laukus
    protected $table = 'follow';
    protected $fillable = [
        'follower_id',
        'followed_id',
    ];
    //Definē relācijas ar citām datu bāzes tabulām
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }
    public function followed()
    {
        return $this->belongsTo(User::class, 'followed_id');
    }
}
