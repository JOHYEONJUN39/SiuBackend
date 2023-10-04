<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Helpers\params;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        try {
            $user = User::where('id', $id)->firstOrFail();
        } catch(ModelNotFoundException $e) {
            $errMsg = $e->getMessage();
            return response()->json(['error' => $errMsg], 500);
        }

        return response()->json($user);
    }

    /** アップデート */
    public function update(Request $request) {
        try {
            $request->validate([
                'id' => 'required|string',
                'nickname' => 'filled|string',
                'password' => 'filled|string', 
                'profile_image' => 'filled|string',
            ]);
        } catch(ValidationException $e) {
            $errMsg = $e->errors();
            $status = $e->status;
            return response()->json(['error' => $errMsg], $status);
        }

        // userId
        $userId = $request->id;
        $updateData = $request->except(['_method', 'id']);

        // ユーザーを見つけなかったら、エラーを投げる
        try {
            $userData = User::find($userId);
        } catch(ModelNotFoundException $e) {
            $errMsg = $e->getMessage();
            return response()->json(['error' => $errMsg], 404);
        }

        foreach ($updateData as $key => $value) {
            $update = ($key == 'password') ? 
                $userData->update(['password' => Hash::make($request->password)]) : 
                $userData->update([$key => $value]);
            if(!$update) {
                return response()->json(['error' => `Failed to update {$key}`]);
            }
        }
        return response()->json(['message' => 'User updated successfully'], 200);
    }

    /**
     * id : string type user id
     * profile_image : profile image file
     */
    public function storePreviewImage(Request $request) {
        try {
            $request->validate([
                'id' => 'required|string',
                'profile_image' => 'required|file',
            ]);
        } catch(ValidationException $e) {
            $errMsg = $e->getMessage();
            $status = $e->status;
            return response()->json(['error' => $errMsg], $status);
        }
        $userId = $request->id;
        $profileImage = $request->profile_image;

        $path = $this->imageHelper->storeImage($profileImage, $userId, params::profile);
        if($path) {
            return response()->json(['profile_image' => $path]);
        }
        return response()->json(['error' => 'Failed to upload preview image']);
    }

    /** 
     * 'profile_image' : string type file path
     */
    public function destroyPreviewImage(Request $request) {
        try {
            $request->validate([
                'profile_image' => 'required|string',
            ]);
        } catch(ValidationException $e) {
            $errMsg = $e->getMessage();
            $status = $e->status;
            return response()->json(['error' => $errMsg], $status);
        }
        $profileImage = $request->profile_image;
        $destroy = $this->imageHelper->destroyImage($profileImage);
        if($destroy) {
            return response()->json(['message' => 'preview image deleted successfully']);
        }
        return response()->json(['error' => 'Failed to upload preview image']);
    }
}
