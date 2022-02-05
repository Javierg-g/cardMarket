<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
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
        Log::info('- Entrada middleware sesión -');
        if (isset($request->api_token)) {
            Log::debug('Llega token');
            $tokenForApi = $request->api_token;
            if(User::where('api_token', $tokenForApi)->first()){
                Log::debug('Usuario encontrado');
                $user = User::where('api_token', $tokenForApi)->first();
                $request->user = $user;
                return $next($request);

            }else{
                Log::error('Token no asociado a ningun usuario/Token incorrecto');
                $response['msg'] = "El token no es correcto";
            }

        } else {
            Log::error('Token no introducido');
            $response['msg'] = "El token no ha sido introducido";
        }
        return response()->json($response);
    }
}
