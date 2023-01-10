<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if (is_null($token)) {
            return response()->json([
                'statut' => 401,
                'error' => "STR_VALID_TOKEN_NEEDED",
            ], 401);
        }
        $result = DB::select('select * from personal_access_tokens where token = :token LIMIT 1', ['token' => $token]);
        
        if (count($result) > 0 && !$this->tokenExpired($result[0])) {
            return $next($request);
        } else {
            return response()->json([
                'statut' => 401,
                'error' => "STR_VALID_TOKEN_NEEDED",
            ], 401);
        }
    }

    /**
     * Determine if the token has expired.
     *
     * @param  array  $token
     * @return bool
     */
    protected function tokenExpired($token)
    {
        if (is_null($token->expires_at)) {
            return false;
        }
        $expirationTime = strtotime($token->expires_at);
        return $expirationTime < $this->getCurrentTime();
    }
}
