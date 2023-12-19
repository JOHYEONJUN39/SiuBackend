<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Models\Comment;
use App\Models\Like;
use App\Helpers\ImageHelper;
use App\Models\PostImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Helpers\params;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct(protected ImageHelper $imageHelper) {

    }
    // 게시글 등록
    public function createPost(Request $request)
    {
        $request->validate([
            "user_id" => "string",
            "title" => "string",
            "article" => "string",
            "tags" => "array",  // array형으로 받음
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

    // 이미지 AWS (S3)에 저장
    public function createImage(Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        
        $imageName = time();
        $image = $request->image;
        // 이미지를 AWS 에 저장하고 imagePath를 반환
        $path = $this->imageHelper->storeImage($image, $imageName, params::post);
        return response()->json(['image_path' => $path]);
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

        // 게시글 내용에서 img 태그 안에있는 img 가져와서 img aws 에서 삭제
        $article = $post->article;

        $pattern = '/src="([^"]+)"/';

        preg_match_all($pattern, $article, $matches);

        // 일치하는 부분이 있다면, 그 다음에 오는 문자열을 $result에 저장합니다.
        if (isset($matches[1])) {
            $result = $matches[1];
        } else {
            // 일치하는 부분이 없을 경우, 빈 문자열이나 다른 처리를 수행할 수 있습니다.
            $result = 'No match found';
        }

        foreach($result as $img) {    
            $this->imageHelper->destroyImage($img);
        }

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
        if(Auth::check()){
            $loginId = Auth::user()->id;
        } else {
            $loginId = "";
        }

        // 요청된 ID에 해당하는 게시글 탐색
        $post = Post::find($id);

        if (!$post) {
            // 게시글이 존재하지 않을 경우 404 에러 반환 또는 다른 처리
            return response()->json(['message' => '게시글을 찾을 수 없습니다.'], 404);
        }

        // 조회수 증가
        $post->view++;
        $post->save();

        // 사용자 정보 넘겨주기
        $userId = $post->user_id;
        $userData = User::find($userId);

        $tagList =  $post->tags;
        $postData = $post->only(
            'id',
            'title',
            'article',
            'view', 
            'user_id', 
            'created_at', 
            'updated_at'
        );
        $tags = $tagList->pluck('tag_name');

        // 댓글 가져오기
        $commentData = Comment::with(['user'])->where('post_id',$id)->get();

        foreach($commentData as $comment){
            $likedList = Like::where('comment_id',$comment->id)->where('user_id',$loginId)->get();
            if($likedList->count() === 0){
                $comment->liked = false;
            }else{
                $comment->liked = true;
            }
        }
        
        // 각각 댓글에 좋아요를 누른 사람의 데이터를 로그인된 데이터와 비교하여 존재하면 true 아니면 false 를 반환하라
        
        // 응답을 Json으로 생성
        return response()->json(["post" => $postData, "user" => $userData, "tags" => $tags, "comments" => $commentData]);
    } 

    // 특정 태그로 조회
    public function retrievePostTagId(Request $request){
        if(Auth::check()){
            $loginId = Auth::user()->id;
        } else {
            $loginId = "";
        }

        $tagName = $request->input('tag');
        // 요청 받은 태그를 가진 게시물을 검색
        $tag = Tag::where('tag_name', $tagName)->first();

        if(!$tag){
            return response()->json(['message' => '태그를 찾을 수 없습니다.'], 404);
        }
        
        $result = Post::with(['tags', 'user', 'comments.user'])
        ->whereHas('tags', function ($query) use ($tagName) {
            $query->where('tag_name', $tagName);
        })
        ->paginate(10);

        $commentData = $result->flatMap(function ($post) {
            return $post->comments;
        });

        foreach($commentData as $comment){
            $likedList = Like::where('comment_id',$comment->id)->where('user_id',$loginId)->get();
            if($likedList->count() === 0){
                $comment->liked = false;
            }else{
                $comment->liked = true;
            }
        }
        return response()->json($result);
    }

    // 조회수 순 정렬 조회
    public function retrievePostView(){
        if(Auth::check()){
            $loginId = Auth::user()->id;
        } else {
            $loginId = "";
        }
        $posts = Post::with(['tags', 'user', 'comments.user'])->orderBy('view','desc')->paginate(10);

        $commentData = $posts->flatMap(function ($post) {
            return $post->comments;
        });

        foreach($commentData as $comment){
            $likedList = Like::where('comment_id',$comment->id)->where('user_id',$loginId)->get();
            if($likedList->count() === 0){
                $comment->liked = false;
            }else{
                $comment->liked = true;
            }
        }

        return response()->json($posts);
    }

    // 최근 순 정렬 조회
    public function retrieveRecentPost(){
        if(Auth::check()){
            $loginId = Auth::user()->id;
        } else {
            $loginId = "";
        }

        $posts = Post::with(['tags', 'user', 'comments.user'])->orderBy('created_at','desc')->paginate(10);

        $commentData = $posts->flatMap(function ($post) {
            return $post->comments;
        });

        foreach($commentData as $comment){
            $likedList = Like::where('comment_id',$comment->id)->where('user_id',$loginId)->get();
            if($likedList->count() === 0){
                $comment->liked = false;
            }else{
                $comment->liked = true;
            }
        }
        
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

    /* 게시글 검색 */
    // 제목+내용 연관어 검색
    public function search($search){
        if(Auth::check()){
            $loginId = Auth::user()->id;
        } else {
            $loginId = "";
        }

        $posts = Post::with(['tags','user','comments.user'])->where('title','like',"$search%")
                 ->orWhere('article','like',"%$search%")
                 ->paginate(10);

        $commentData = $posts->flatMap(function ($post) {
            return $post->comments;
        });

        foreach($commentData as $comment){
            $likedList = Like::where('comment_id',$comment->id)->where('user_id',$loginId)->get();
            if($likedList->count() === 0){
                $comment->liked = false;
            }else{
                $comment->liked = true;
            }
        }
        
        return response()->json($posts);
    }

    // 연관 태그 검색
    public function relatedPostTags($tag){
        // 태그와 연관된 게시물을 가져옴
        $posts = Tag::where('tag_name', 'like', "$tag%")->simplePaginate(10);

        return response()->json(['tags' => $posts->map(function($post) {return $post->tag_name;})]);
    }

    // 유저 아이디로 게시글 검색
    public function userPosts($userId) {
        if(Auth::check()){
            $loginId = Auth::user()->id;
        } else {
            $loginId = "";
        }

        // 해당 유저의 게시글을 가져옴
        $posts = Post::with('tags','user','comments.user')
                 ->where('user_id', 'like', "$userId")
                 ->orderBy('created_at', 'desc')
                 ->paginate(10);

        $commentData = $posts->flatMap(function ($post) {
            return $post->comments;
        });

        foreach($commentData as $comment){
            $likedList = Like::where('comment_id',$comment->id)->where('user_id',$loginId)->get();
            if($likedList->count() === 0){
                $comment->liked = false;
            }else{
                $comment->liked = true;
            }
        }
        return response()->json($posts);
        
    } 
}
