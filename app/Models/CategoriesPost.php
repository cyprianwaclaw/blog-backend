<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriesPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'post_id',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);

    }

}
