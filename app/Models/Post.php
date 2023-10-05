<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'article',
        'user_id',
        'view',
    ];

    // Post 모델과 Tag 모델 간의 다대다 관계
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags', 'post_id', 'tag_id');
    } 

    // Post 모델과 PostImage 모델 간의 일대다 관계 정의
    public function images()
    {
        return $this->hasMany(PostImage::class, 'post_id');
    }

    // post 모델과 comment 모델의 일대다 관계 정의
    public function comments()
    {
        return $this->hasMany(Comment::class);                          
    }              

    // user 모델과 post 모델의 일대다 관계 정의
    public function user()
    {
        return $this->belongTo(User::class);
    }

}
