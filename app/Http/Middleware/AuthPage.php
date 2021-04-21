<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Custom\Common\CustomCommon;
use App\Custom\CheckLogin\CheckLogin;
use App\Custom\CheckSt\CheckSt;

class AuthPage
{

    /**
     * 页面鉴权，是否已登录，未登录，是否有ST
     * 1. 已登录，下一步
     * 2. 未登录，是否有ST，有验证，无，重定向
     * 3. ST验证成功，下一步，失败，重定向
     */
    public function handle(Request $request, Closure $next) {

        // 已登录，下一步
        if (CheckLogin::handle()) return $next($request);
        
        // 未登录，验证ST
        if (CheckSt::handle($request)) {

            // 删掉st
            $uri = $request->getUri();

            $redirectUrl = CustomCommon::delQuery($uri, ['st']);

            return redirect($redirectUrl);
        }

        // ST不可用，重定向
        $uri = $request->getUri();
        $redirectUrl = config('custom.sso.login') . "?serve=$uri";

        return redirect()->away($redirectUrl);
    }
}
