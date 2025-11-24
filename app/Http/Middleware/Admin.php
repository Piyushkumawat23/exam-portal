<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized: Please login'], 401);
        }

        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden: Admin access only'], 403);
        }

        return $next($request);
    }
}