<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    //
    public function logout(Request $request) {
        // 왜 sanctum 가드가 아니라 web을 사용하는거지
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        return response()->json(['message' => 'logged out successfully']);
    }
}
