<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class TokenValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if (isset($request->api_token)) {
            $tokenForApi = $request->api_token;
            if($user = User::where('api_token', $tokenForApi)->first()){
                $user = User::where('api_token', $tokenForApi)->first();
                $request->user = $user;
                return $next($request);

            }else{
                $response['msg'] = "El token no es correcto";
            }

        } else {
            $response['msg'] = "El token no ha sido introducido";
        }
        return response()->json($response);
    }
}
