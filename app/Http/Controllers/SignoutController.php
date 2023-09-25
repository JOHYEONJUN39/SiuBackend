<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SignoutController extends Controller
{

    public function __construct(protected LogoutController $logoutController, protected UserController $userController) {

    }

    public function __invoke(Request $request) {
       $this->logoutController($request);
       $destroy = $this->userController->destroy($request->id);

       if($destroy->status() == 200) {
        return response()->json(['message' => 'Signed out successfully']);
       }

       return response()->json(['message' => 'Failed to signout']);
    }
}
