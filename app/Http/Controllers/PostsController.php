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
        // 사용자 정보 넘겨주기
        
        // 조회수 증가
        $post->view++;
        $post->save();

        // 응답을 Json으로 생성
        return response()->json(['post' => $post]);
    }

    // 특정 태그로 조회
    public function retrievePostTagId(Request $request){

        $tagName = $request->input('tag');
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

    // 게시글 수정
    // 제목만 수정 / 내용만 수정 / 태그 삭제 / 태그 추가
    public function updatePost(Request $request, $id){
        $post = Post::find($id);
        
        if(!$post) {
            return response()->json(['message' => '게시글을 찾을 수 없습니다.'], 404);
        }

        // 요청에서 수정할 데이터를 추출
        $title = $request->input('title');
        $article = $request->input('article');
        $tagData = $request->input('tags',[]);

        // 원래 게시글의 태그 확인
        // pluck 함수는 DB 결과 집합에서 원하는 컬럼의 값을 추출하는데 사용됨
        // $oldTags = $post->tags->pluck('tag_name')->toArray();

        $existingTagIds = $post->tags->pluck('id')->toArray();
        // 새로운 태그 목록으로 부터 태그 Id를 가져옴

        $newTagIds = [];
        foreach($tagData as $tagName) {
            $tag = Tag::firstOrCreate(['tag_name' => $tagName]);
            $newTagIds[] = $tag->id;
        }

        // 게시글의 태그를 새로운 태그로 업데이트
        $post->tags()->sync($newTagIds);

        // 연결되지 않은 기존 태그 삭제
        // $existingTagIds 와 $newTagIds를 비교 후 $existingTagIds에만 존재하는 값을 삭제
        $tagsToDelete = array_diff($existingTagIds, $newTagIds);
        foreach ($tagsToDelete as $tagId) {
            $post->tags()->detach($tagId);
        }

        // 게시글과 엮인 태그 확인 및 해당 태그들을 가진 게시물이 없을 시 삭제
        $tags = $post->tags;

        // 태그 중 post_tags 테이블에 사용 되지 않는 태그 삭제
        foreach($tags as $tag) {
            if(!$tag->posts()->exists()) {
                // post_tags 테이블에 사용되지 않는 태그 삭제
                $tag->delete();
            }
        }

        // 제목만 수정
        if (!is_null($title)) {
            $post->title = $title;
        }

        // 내용만 수정
        if (!is_null($article)) {
            $post->article = $article;
        }


        $post->save();

        return response()->json(['message' => '게시글이 성공적으로 수정되었습니다.']);
    }
}
