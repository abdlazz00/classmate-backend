<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nim' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('nim', $request->nim)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'NIM atau Password Salah'
            ], 401);
        }

        $token = $user->createToken('mobile')->plainTextToken;
        return [
            'token'=>$token,
            'user' => $user,
        ];

    }
}
