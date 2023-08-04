<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-03 15:41:08
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-03 15:46:21
 */


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Session()->has('loginId')) {
            return redirect('login')->with('fail', 'You must be logged in');
        }
        return $next($request);
    }
}
