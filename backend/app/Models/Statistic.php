<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    use HasFactory;
    //Norāda datu bāzes tabulas nosaukumu un aizpildāmos laukus
    protected $fillable =[
        'post_id',
        'comment',
        'like',
        'repost',
        'views'
    ];
    //Definē relācijas ar citām datu bāzes tabulām
    public function post(){
        return $this->belongsTo(Post::class);
    }
}
