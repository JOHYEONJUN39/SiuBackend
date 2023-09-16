<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function __construct(protected ImageHelper $imageHelper) {
    }
    /** 現在ユーザー */
    public function index(Request $request) {
        return $request->user();
    }
    /** 特定ユーザー */
    public function show($id) {
        $userData = User::where('id', $id)->firstOrFail();
        return $userData;
    }

    /** アップデート */
    public function update(Request $request) {
        try {
            $request->validate([
                'id' => 'required',
                'nickname' => 'filled',
                'password' => 'filled', 
                'profileImage' => 'filled',
            ]);
        } catch(ValidationException $e) {
            $errMsg = $e->errors();
            $status = $e->status;
            return response()->json(['error' => $errMsg], $status);
        }
        // userId
        $userId = $request->id;
        // update
        switch(true) {
            // update Nickname
            case isset($request->nickname) :
            User::where('id', $userId)->update(['nickname' => $request->nickname]);
            // update Password
            case isset($request->password) : 
                User::where('id', $userId)->update(['password' => Hash::make($request->password)]);
            // update ProfileImage
            case isset($request->profileImage) :
                $path = $this->imageHelper->storeProfileImage($request->profileImage, $userId);
                User::where('id', $userId)->update(['profile_image' => $path]);
                break;
        }

        return response()->json(['message' => 'User updated successfully'], 200);
    }
    /** ユーザー削除 */
    public function destroy($id) {
        User::destroy($id);
        return response()->json(['message' => 'User deleted successfully'] ,200);
    }
}
