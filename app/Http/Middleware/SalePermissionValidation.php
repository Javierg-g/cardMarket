<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SalePermissionValidation
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
        if ($request->user->role =='Profesional' || $request->user->role =='Particular') {
            return $next($request);
        } else {
            $response['msg'] = "El usuario actual no posee los permisos necesarios";
        }
        return response()->json($response);
    }
}
