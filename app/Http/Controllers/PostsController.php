<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Posttag;

class PostsController extends Controller
{
    // 게시글 등록
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
            // post로 받은 tag명과 같은 이름을 tag 테이블에서 찾고 없다면 저장 시킴
            $tag = Tag::firstOrCreate(['tag_name' => $tagName]);
            $tags[] = $tag->id;
        }

        // 게시물(Post)과 태그(Tag)의 관계 설정
        $post->tags()->sync($tags);

        return response()->json($post);
    }

    // 게시글 삭제
    public function deletePost($id) {
        // 요청된 ID에 해당하는 게시글 탐색
        $post = Post::find($id);

        // 게시글이 존재하지 않을 때 
        if (!$post) {
            return response()->json(['message' => '게시글이 존재하지 않습니다.'], 404);
        }

        // 게시글과 엮인 태그 확인 및 해당 태그들을 가진 게시물이 없을 시 삭제
        $tags = $post->tags;

        // 게시글 삭제
        $post->delete();

        // 태그 중 post_tags 테이블에 사용 되지 않는 태그 삭제
        foreach($tags as $tag) {
            if(!$tag->posts()->exists()) {
                // post_tags 테이블에 사용되지 않는 태그 삭제
                $tag->delete();
            }
        }

        // 삭제 성공 시 동작
        return response()->json(['message' => '게시글이 성공적으로 삭제 되었습니다.']);
    }

    // 게시글 조회
    // 특정 id로 조회

    public function retrievePostId($id){
        // 요청된 ID에 해당하는 게시글 탐색
        $post = Post::find($id);

        if (!$post) {
            // 게시글이 존재하지 않을 경우 404 에러 반환 또는 다른 처리
            return response()->json(['message' => '게시글을 찾을 수 없습니다.'], 404);
        }

        // 응답을 Json으로 생성
        return response()->json(['post' => $post]);
    }

    // 특정 태그로 조회
    public function retrievePostTagId(Request $request){
        $tagName = $request->input('tag_name');

        // 요청 받은 태그를 가진 게시물을 검색
        $tag = Tag::where('tag_name', $tagName)->first();

        if(!$tag){
            return response()->json(['message' => '태그를 찾을 수 없습니다.'], 404);
        }

        // 해당 태그와 연결된 게시글을 가져옴
        $posts = $tag->posts;

        return response()->json(['posts' => $posts]);
    }

    // 조회수 순 정렬 조회
    public function retrievePostView(){
        $posts = Post::orderBy('view','desc')->get();

        return response()->json($posts);
        // return response()->json(['message' => '조회순 정렬']);
    }

    // 최근 순 정렬 조회
    public function retrieveRecentPost(){
        $posts = Post::orderBy('created_at','desc')->get();

        return response()->json($posts);
    }
}
