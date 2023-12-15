<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    //Norāda datu bāzes tabulas nosaukumu un aizpildāmos laukus
    protected $table = 'comment';
    protected $fillable =[
        'user_id',
        'text',
        'fileFormat',
        'media',
        'stat_id',
        'post_id',
        'fileSize',
        'fileName'
    ];
    //Definē relācijas ar citām datu bāzes tabulām
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function post(){
        return $this->belongsTo(Post::class);
    }
}
