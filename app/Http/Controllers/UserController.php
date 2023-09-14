<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //아이디 중복 검사 API
    public function store(Request $request) {
        $request->validate([
            'id' => 'required',
            'password' => 'required',
        ]);

        // when user registered, default nickname is userId
        User::create([
            'id' => $request->id,
            'nickname' => $request->id,
            'password' => $request->password,
            'profile_image' => env('DEFAULT_PROFILE_IMAGE_PATH'),
        ]);
        return response()->json(['message' => 'User add successfully'], 201);        
    }

    public function show($id) {
        $userData = User::where('id', $id)->firstOrFail();
        return $userData;
    }

    public function update(Request $request) {
        $request->validate([
            'id' => 'required',
        ]);

        // 개선 필요
        if(isset($request->nickname)) {
            User::where('id', $request->id)->update(['nickname' => $request->nickname]);
        }

        if(isset($request->password)) {
            User::where('id', $request->id)->update(['password' => Hash::make($request->password)]);
        }

        /** store user profile image */
        if(isset($request->profileImage)) {
            $imageHelper = new ImageHelper();
            $path = $imageHelper->storeProfileImage($request->profileImage, $request->id);
            User::where('id', $request->id)->update(['profile_image' => $path]);
        }

        return response()->json(['message' => 'User updated successfully'], 200);
    }

    public function destroy($id) {
        User::destroy($id);
        return response()->json(['message' => 'User deleted successfully'] ,200);
    }
}
