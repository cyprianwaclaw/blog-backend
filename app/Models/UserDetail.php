<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'about_user'
    ];

    // public function user()
    // {
    //     return $this->hasOne(Post::class);
    // }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
