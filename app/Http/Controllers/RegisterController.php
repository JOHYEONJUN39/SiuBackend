<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function __invoke(Request $request) {

        try {
            $validated = $request->validate([
                'id' => 'required|unique:users,id',
                'password' => 'required',
            ]);
        } catch(ValidationException $e) {
            $errMsg = $e->errors();
            $status = $e->status;
            return response()->json(['error' => $errMsg], $status);
        }

        // when user registered, default nickname is userId
        $user = User::create([
            'id' => $validated['id'],
            'nickname' => $validated['id'],
            'password' => $validated['password'],
            'profile_image' => env('DEFAULT_PROFILE_IMAGE_PATH'),
        ]);
        if(!$user) {
            return response()->json(['error' => 'Falied to add user'], 500);
        }
        return response()->json(['message' => 'User add successfully'], 201);     
    }
}
