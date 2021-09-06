<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LastOnlineTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($user = Auth()->user()){
            $time = now();
            $user->update(['last_online_time'=>$time]);
        }
        return $next($request);
    }
}
