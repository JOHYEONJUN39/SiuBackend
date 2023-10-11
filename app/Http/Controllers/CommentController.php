<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    // comment 등록
    public function createComment(Request $request){
        $request->validate([
            "post_id" => "integer",
            "user_id" => "string",
            "comment" => "string",
        ]);
        
        try {
            $comment = Comment::create([
                'comment' => $request->input('comment'),
                'user_id' => $request->input('user_id'),
                'post_id' => $request->input('post_id'),
            ]);
            
            return response()->json(['message' => '게시되었습니다.', 'comment' => $comment], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => '등록에 실패했습니다.', 'message' => $e->getMessage()],500);
        }
    }

    
    // comment 수정
    public function updateComment(Request $request){
        $request->validate([
            "comment_id" => "integer",
            "comment" => "string",
        ]);

        $commentId = $request->comment_id;
        $comment = Comment::find($commentId);

        if(!$comment) {
            return response()->json(['message' => '댓글을 찾을 수 없습니다.'], 404);
        }

        $comment->comment = $request->comment;

        $comment->save();

        return response()->json(['message' => '댓글 수정 성공']);
    }
    
    // comment 삭제
    public function deleteComment(Request $request){
        $comment = Comment::find($request->comment_id);

        if (!$comment){
            return response()->json(['message' => '댓글이 존재하지 않습니다.'], 404);
        }
        $comment->delete();

        return response()->json(['message' => '댓글 삭제 성공']);
    }

    // 좋아요
    public function like(Request $request){
        $request->validate([
            "comment_id" => "integer",
            "user_id" => "string"
        ]);

        $commentId = $request->comment_id;
        $userId = $request->user_id;

        $checkDupLike = Like::where('user_id',$userId)
                            ->where('comment_id',$commentId)
                            ->get();
        
        if(!$checkDupLike->isEmpty()) {
            return response()->json(['message' => '이미 좋아요한 댓글입니다.']);
        }

        try{
            $like = Like::create([
                "user_id" => $userId,
                "comment_id" => $commentId
            ]);

            // 댓글 좋아요 수
            $likeCount = DB::table('likes')
                           ->where('comment_id',$commentId)
                           ->groupBy('comment_id')
                           ->count();
            
            // 댓글 좋아요 수 업데이트
            Comment::where('id',$commentId)
                ->update([
                    'like_count' => $likeCount
                ]);
            return response()->json(['message' => '좋아요 성공.'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => '좋아요 실패.', 'message' => $e->getMessage()],500);
        }
        
    }

    // 좋아요 취소
    public function unlike(Request $request){
        $request->validate([
            "like_id" => "integer",
        ]);

        $likeId = $request->like_id;
        $like = Like::find($likeId);
        
        if(!$like) {
            return response()->json(['message' => '좋아요 기록이 없습니다.'], 404);
        }
        $commentId = $like->comment_id;
        $like->delete();

        // 댓글 좋아요 수
        $likeCount = DB::table('likes')
        ->where('comment_id',$commentId)
        ->groupBy('comment_id')
        ->count();

        // 댓글 좋아요 수 업데이트
        Comment::where('id',$commentId)
        ->update([
            'like_count' => $likeCount
        ]);
        
        return response()->json(['message' => '좋아요 취소 성공']);
    }
}
