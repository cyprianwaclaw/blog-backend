<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'hero-image',
        'link',
        'user_id',
        'description'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function postDetails()
    {
        return $this->hasMany(PostDetails::class);
        // return $this->belongsToMany(PostDetails::class,'post_details', 'post_id');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_posts', 'post_id', 'category_id')
        ->select('id', 'name', 'link');
    }
    // public function categories()
    // {
    //     return $this->hasMany(Category::class);
    //     // return $this->belongsToMany(PostDetails::class,'post_details', 'post_id');
    // }
}
