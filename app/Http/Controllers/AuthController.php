<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request){
        $validated = Validator::make($request -> all(), [
            'name' => 'required|string|max:255', 
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);

        if($validated->fails()){
            return response()->json($validated->errors(), 403);
        }

      try {
       $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
       ]);

       return response()->json([
        'message' => "Registration Successful",
        'user'=> $user
       ], 200);
      } catch (\Exception $exception) {
       return response()->json(['error' => $exception->getMessage()]);
      }

    }

    public function login(Request $request){
        $validated = Validator::make($request -> all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if($validated->fails()){
            return response()->json($validated->errors(), 403);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        try {
            if(!Auth::attempt($credentials)){
                return response()->json(['error' => "Invalid credentials"]);
            }

            $user = User::where('email', $request->email)->firstOrFail();

            $token = $user-> createToken('auth_token') ->plainTextToken;

            return response()->json([
                'message' => "Login Successful",
                'token' => $token,
                'user'=> $user
               ], 200);
         
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()]);
        }
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => "Logout successfully",
           ], 200);
    }
}
