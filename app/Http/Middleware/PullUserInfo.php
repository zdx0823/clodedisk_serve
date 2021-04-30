<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Custom\PullUserInfo\PullUserInfo as tPullUserInfo;

class PullUserInfo
{

    
    public function handle(Request $request, Closure $next)
    {
        tPullUserInfo::handle();
        return $next($request);
    }
}
