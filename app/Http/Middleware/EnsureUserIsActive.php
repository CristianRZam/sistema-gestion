<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && !$user->activo) {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta ha sido deshabilitada. Contacta al administrador.',
            ]);
        }

        return $next($request);
    }
}
