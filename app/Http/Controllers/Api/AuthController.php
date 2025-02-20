<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;



class AuthController extends Controller
{


    public function login(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to authenticate the user
        if (!Auth::attempt($validatedData)) {
            return response(['message' => 'Invalid credentials'], 401);
        }

    // Retrieve the authenticated user
    $user = Auth::user();

    // Revoke all existing tokens for the user
// Revoke all existing tokens for the user
DB::table('oauth_access_tokens')
    ->where('user_id', $user->id)
    ->update(['revoked' => true]);

    // Create a new access token for the user
    $accessToken = $user->createToken('authToken')->accessToken;

    // Load the 'employee' relationship and retrieve role names
    $user->load('employee');
    $roles = $user->getRoleNames(); // Returns a collection of role names

    // Construct a custom user data array
    $userData = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => $user->avatar_url,
        // Include other attributes as needed
        'employee' => $user->employee, // Include employee relationship
        'roles' => $roles, // Include roles
        // Exclude the 'tokens' relationship
    ];

    // Return the custom user data along with the access token
    return response([
        'user' => $userData,
        'access_token' => $accessToken,
    ]);
    }


    public function logout(Request $request){
        $request->user()->token()->revoke();
        return response(['message' => 'Successfully logged out']);
    }


}
