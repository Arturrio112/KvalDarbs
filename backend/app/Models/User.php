<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 
     *
     * @var array<int, string>
     */
    //Norāda datu bāzes tabulas nosaukumu un aizpildāmos laukus
    protected $table = 'user';
    protected $fillable = [
        'name',
        'email',
        'password',
        'birthdate'
    ];

    /**
     * 
     *
     * @var array<int, string>
     */
    //Atribūti, kas ir paslēpti
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    //Definē relācijas ar citām datu bāzes tabulām
    public function profile(){
        return $this->hasOne(Profile::class);
    }
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function like(){
        return $this->hasMany(Like::class);
    }
    public function repost(){
        return $this->hasMany(Repost::class);
    }
    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_id');
    }

    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }
    public function userConversation(){
        return $this->hasMany(UserConversation::class);
    }
    public function message(){
        return $this->hasMany(Message::class);
    }
}
