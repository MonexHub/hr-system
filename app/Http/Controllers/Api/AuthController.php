<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{


    public function login(Request $request){

        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($validatedData)) {
            return response(['message' => 'Invalid credentials'],401);
        }else{
            $accessToken = Auth::user()->createToken('authToken')->accessToken;
            return response(['user' => Auth::user()->load('employee'),
            'access_token' => $accessToken]);
        }
    }


    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response(['message' => 'Successfully logged out']);
    }


}
