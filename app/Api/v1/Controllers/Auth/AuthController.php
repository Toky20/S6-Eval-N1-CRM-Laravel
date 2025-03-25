<?php
namespace App\Api\v1\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('SpringBootAccess')->accessToken;
            return response()->json(['token' => $token],200);
        }

        // Retourner une réponse JSON cohérente en cas d'erreur
        return response()->json([
            'error' => 'Authentication failed',
            'success' => false
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['message' => 'Successfully logged out']);
    }
}