<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::user()->roles()->first();
        if (Auth::user() &&  $admin->role_name == 'admin')
        {
            return $next($request);
        }

        Auth::guard('web')->logout();
        return redirect('login')->with('status','You have not admin access');
    }
}
