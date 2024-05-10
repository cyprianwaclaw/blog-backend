<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    protected $appends = ['comments_count', 'read_time', 'post_date', 'user'];

    public function getCommentsCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getReadTimeAttribute()
    {
        return 13;
    }

    public function getUserAttribute()
    {
        return  $this->user()->select('name', 'link', 'image')->get();
    }

    //! Auto added upload date to all post
    public function getPostDateAttribute()
    {
        return $this->created_at->format('d.m.Y');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            // get: fn (string $value) => ucfirst($value),
            // set: fn (string $value) => strtolower($value),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function postDetails()
    {
        return $this->hasMany(PostDetails::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_posts', 'post_id', 'category_id')
            ->select('id', 'name', 'link');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeCategoryByName1($query, string $category_name)
    {
        $category_id = Category::where('name', $category_name)->value('id');
        if ($category_id === null) {
            return "Category not exist";
        }
        $postInCategory = CategoriesPost::where('category_id', $category_id)
            ->whereHas('post', function ($query) {
                $query->where('status', 'published');
            })
            ->get();
        return $postInCategory->map(function ($categoryPost) {
            return [
                'name' => $categoryPost->post->name,
                'date' => $categoryPost->post->post_date,
                'user' => $categoryPost->post->user,
                'status' => $categoryPost->post->status,
                'read_time' => $categoryPost->post->read_time,
            ];
        });
    }
    // public function scopeCategoryByName($query, string $category_name, array $filters = [])
    // {
    //     $category_id = Category::where('name', $category_name)->value('id');

    //     if ($category_id === null) {
    //         return "Category not exist";
    //     }

    //     $query = CategoriesPost::where('category_id', $category_id);

    //     // Dodaj dodatkowe warunki do zapytania, jeśli przekazano filtry
    //     if (!empty($filters)) {
    //         if (isset($filters['status'])) {
    //             $query->whereHas('post', function ($query) use ($filters) {
    //                 $query->where('status', $filters['status']);
    //             });
    //         }
    //         if (isset($filters['read_time'])) {
    //             $query->whereHas('post', function ($query) use ($filters) {
    //                 $query->where('read_time', $filters['read_time']);
    //             });
    //         }
    //         // Dodaj inne filtry, jeśli są potrzebne
    //     }

    //     $postInCategory = $query->get();

    //     return $postInCategory->map(function ($categoryPost) {
    //         return [
    //             'name' => $categoryPost->post->name,
    //             'date' => $categoryPost->post->post_date,
    //             'user' => $categoryPost->post->user,
    //             'status' => $categoryPost->post->status,
    //             'read_time' => $categoryPost->post->read_time,
    //         ];
    //     });
    // }

    public function scopeCategoryByName($query, string $category_name, array $filters = [])
    {
        $category_id = Category::where('name', $category_name)->value('id');

        if ($category_id === null) {
            return "Category not exist";
        }

        $query = CategoriesPost::where('category_id', $category_id);

        // Dodaj dodatkowe warunki do zapytania, jeśli przekazano filtry
        if (!empty($filters)) {
            if (isset($filters['status'])) {
                $query->whereHas('post', function ($query) use ($filters) {
                    $query->where('status', $filters['status']);
                });
            }
            // Dodaj inne filtry, jeśli są potrzebne
        }

        $postInCategory = $query->get();

        // Filtruj wyniki na podstawie wartości read_time
        $filteredPosts = $postInCategory->filter(function ($categoryPost) use ($filters){
            return $categoryPost->post->read_time == $filters['read_time']; // Tutaj sprawdzam, czy read_time wynosi 13
        });

        return $filteredPosts->map(function ($categoryPost) {
            return [
                'name' => $categoryPost->post->name,
                'date' => $categoryPost->post->post_date,
                'user' => $categoryPost->post->user,
                'status' => $categoryPost->post->status,
                'read_time' => $categoryPost->post->read_time,
            ];
        });
    }


}
