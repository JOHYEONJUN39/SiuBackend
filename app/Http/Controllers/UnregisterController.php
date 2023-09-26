<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Http\Controllers\LogoutController;
use App\Models\User;
use Illuminate\Http\Request;

class UnregisterController extends Controller
{
    public function __construct(
        protected ImageHelper $imageHelper, 
        public LogoutController $logoutController) {
    }
    /** ユーザー削除 */
    public function __invoke(Request $request) {
        $userId = $request->id;
        $user = User::find($userId);
        if($user) {
            $this->imageHelper->destroyImage($user->profile_image);
            // 이미지 저장 실패 시 어떻게 처리하지
            $user->delete(); 
            $this->logoutController->__invoke($request);
            return response()->json(['message' => 'Unregister successfully']);
        }
        return response()->json(['error' => 'Failed to delete User'], 500);
    }  
}
