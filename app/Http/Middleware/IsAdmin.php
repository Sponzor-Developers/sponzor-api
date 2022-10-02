<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\JsonController;


class IsAdmin
{
    /**
     * Undocumented function
     *
     * @param [type] $request
     * @param Closure $next
     * @return void
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user()->is_admin == 1) {
            return JsonController::return('Rota não encontrada', 404);
        }
        return $next($request);
    }
}
