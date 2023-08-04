<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-03 15:51:50
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-03 15:57:23
 */


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AlreadyLoggedIn
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Session()->has('loginId') && (url('login') == $request->url() || url('register') == $request->url()))  {
            return back();
        }
        return $next($request);
    }
}
