<?php

namespace App\Http\Controllers;

use App\Helpers\ValidateHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{   
    public function __construct(protected ValidateHelper $valiHelper) {
        
    }

    public function __invoke(Request $request) {
        try {
            $credentials = $request->validate([
                'id' => 'required',
                'password' => 'required',
            ]);
          } catch(ValidationException $e) {
            $errMsg = $e->errors();
            $status = $e->status;
            return response()->json(['error' => $errMsg], $status);
        }
        
        if(Auth::attempt([
            'id' => $credentials['id'], 
            'password' => $credentials['password']
        ])) {
            $request->session()->regenerate();
            return response()->json($request->user());
        }

        return response()->json([
            'error' => 'The provided credentials do not match our records.',
        ], 400);
    }
}
