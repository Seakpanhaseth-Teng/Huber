<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDriverVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'driver' && !$user->is_verified) {
            return redirect()->route('driver.verification.pending');
        }

        if ($user->role !== 'driver') {
            return redirect()->route('home')->with('error', 'Access denied. Driver privileges required.');
        }

        return $next($request);
    }
}
