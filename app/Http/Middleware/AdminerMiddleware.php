<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-11 08:38:42
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-11 08:38:57
 */


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Add your authentication logic here, e.g., check if the user is an admin
        if (auth()->check() && auth()->user()->isAdmin()) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
