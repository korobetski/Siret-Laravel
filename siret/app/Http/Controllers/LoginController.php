<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

//https://laravel.com/docs/9.x/authentication
class LoginController extends Controller
{

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        if (Auth::attempt($credentials, $request->remember)) {
            $request->session()->regenerate();
            $user = $request->user();

            return response()->json([
                'statut' => '200',
                'datas' => 'Logged in',
                'user' => $user->name,
                'gravatar' => "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $user->email ) ) ) . "?d=retro&s=48",
                'token' => $user->tokens[0]->token,
            ], 200);
        }

        return response()->json([
            'statut' => 400,
            'datas' => 'The provided credentials do not match our records.',
        ], 400);
    }
    
    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'statut' => '200',
            'datas' => 'Logged out',
        ], 200);
    }
}
