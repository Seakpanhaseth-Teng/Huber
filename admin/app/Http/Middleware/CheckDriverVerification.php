<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDriverVerification
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role !== 'driver') {
            return redirect()->route('home')->with('error', 'Access denied. Driver privileges required.');
        }

        if (! $user->is_verified) {
            return redirect()->route('driver.verification.pending');
        }

        return $next($request);
    }
}
