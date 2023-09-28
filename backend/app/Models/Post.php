<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'post';
    protected $fillable =[
        'user_id',
        'text',
        'fileFormat',
        'media',
        'fileName', // Add this line
        'fileSize',
    ];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function statistic(){
        return $this->hasOne(Statistic::class);
    }
    public function comment(){
        return $this->hasMany(Comment::class);
    }
    
    public function like(){
        return $this->hasMany(Like::class);
    }
    public function repost(){
        return $this->hasMany(Repost::class);
    }
}
