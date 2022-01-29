<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class PermissionValidation
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
        if ($request->user->role == "Admin") {
            return $next($request);
        } else {
            $response['msg'] = "El usuario actual no posee los permisos necesarios";
        }
        return response()->json($response);
    }
}
