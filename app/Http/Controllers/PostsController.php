<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Posttag;

class PostsController extends Controller
{
    public function createPost(Request $request)
    {
        $request->validate([
            "user_id" => "string",
            "title" => "string",
            "article" => "string",
            "tags" => "array",  // array형으로 받음
            "view" => "integer",
        ]);

        // Posts 테이블에 저장할 데이터
        $postData = $request->only('user_id','title','article','view');
        $post = Post::create($postData);

        //Tags 테이블에 저장할 데이터
        $tagData = $request->input('tags');
        $tags = [];
        foreach ($tagData as $tagName) {
            $tag = Tag::firstOrCreate(['tag_name' => $tagName]);
            $tags[] = $tag->id;
        }

        // 게시물(Post)과 태그(Tag)의 관계 설정
        $post->tags()->sync($tags);

        return response()->json($post);
    }
}
