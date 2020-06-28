<?php

namespace App\Http\Middleware;

use Closure;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,$apiToken)
    {
        return $apiToken;
        $apiToken= $request->header('token'); // token or whatever you sending key as
        $canAccess = false;
        foreach(User::all() as $user){
            if($user->api_token == $apiToken){
                $canAccess = true;
            }
        }

        if($canAccess == false) {
           abort(403, 'Access denied');
        }
        return $next($request);
    }
}
