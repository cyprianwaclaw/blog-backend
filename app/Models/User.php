<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Post;
use App\Models\SavedPost;
use App\Models\UserDetail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'link',
        'image',
        'password',
    ];

    /**1
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Metoda wykonująca się automatycznie po utworzeniu nowego użytkownika
    protected static function booted()
    {
        static::created(function ($user) {
            // Wykonaj operacje po utworzeniu nowego użytkownika
            DB::table('categories')->insert([
                'name' => $user->email, // Użyj adresu email jako nazwy kategorii
                'link' => $user->email, // Użyj adresu email jako nazwy kategorii
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }


    public function savedPosts()
    {
        return $this->hasMany(SavedPost::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }
}
