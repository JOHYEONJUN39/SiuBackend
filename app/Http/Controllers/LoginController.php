<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //
    public function authenticate(Request $request) {
        // $credentials = $request->validate([
        //     'id' => ['required'],
        //     'password' => ['required'],
        // ]);

        $credentials = $request->only(['id', 'password']);
        

        if(Auth::attempt(['id' => $credentials['id'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            return response()->json(['message' => 'logged in successfully'], 200);
        }

        return response()->json([
            'id' => 'The provided credentials do not match our records.',
        ]);
    }
}
