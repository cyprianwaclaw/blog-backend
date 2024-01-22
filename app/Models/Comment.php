<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    const COMMENT_TYPE_ENUM = ['null', 'like', 'heart', 'haha'];
    protected $fillable = [
        'title',
        'text',
        'post_id',
        'user_id',
        'relaction'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
