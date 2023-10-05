<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;

class CommentController extends Controller
{
    // comment 등록
    public function createComment(Request $request, $postId){
        $request->validate([
            "user_id" => "string",
            "comment" => "string",
        ]);
        
        try {
            $comment = Comment::create([
                'comment' => $request->input('comment'),
                'user_id' => $request->input('user_id'),
                'post_id' => $postId,
            ]);
            
            return response()->json(['message' => '게시되었습니다.', 'comment' => $comment], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => '등록에 실패했습니다.', 'message' => $e->getMessage()],500);
        }
    }

    
    // comment 수정
    public function updateComment(Request $request){
        
    }
    
    // comment 삭제
    public function deleteComment($id){
        $comment = Comment::find($id);

        if (!$comment){
            return response()->json(['message' => '댓글이 존재하지 않습니다.'], 404);
        }
        $comment->delete();

        return response()->json(['message' => '댓글 삭제 성공']);
    }

}
