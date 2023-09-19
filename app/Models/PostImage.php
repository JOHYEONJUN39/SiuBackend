<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostImage extends Model
{
    use HasFactory;

    protected $table = 'post_images';
    protected $fillable = [
        'id',
        'post_id',
        'image_path',
    ];

    public $timestamps = false; // 타임 스탬프 관리 비활성화

    // PostImage 모델과 Post 모델 간의 역참조 (역관계) 정의
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
