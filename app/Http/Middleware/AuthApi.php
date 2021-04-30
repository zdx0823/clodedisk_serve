<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Cookie;

use Illuminate\Http\Request;

use App\Custom\Common\CustomCommon;
use App\Custom\CheckLogin\CheckLogin;
use App\Custom\CheckSt\CheckSt;
use App\Custom\PullUserInfo\PullUserInfo;


class ErrMsg {


    public static function notLogin () {

        $SSO = config('custom.sso.login');

        // json返回值
        $res = CustomCommon::makeErrRes(
            '未登录，请登录后操作', [], 
            [ 'sso' => $SSO ],
            -2,
        );

        return $res;
    }


    public static function notSecVerify () {

        return CustomCommon::makeErrRes(
            '需要二次认证，请输入邮箱验证码', [], [], -3
        );
    }
}


/**
 * api鉴权，检查用户是否有权限
 * 1. 是否已登录
 * 2. 拉取用户信息存储到session
 * 3. 未登录返回重定向的url
 */
class AuthApi {

    private const S_FORBIDDEN = '您无权限访问此页面';


    /**
     * 是否为管理员
     * 1. 不是，下一步
     * 2. 是，有没有做二次验证
     * 2-1. 无，返回错误值
     * 2-2. 有，下一步
     * 
     * 返回true，false，错误提示语
     */
    private static function isAdmin ($userInfo) {

        if (!$userInfo['isAdmin']) return false;

        $loggedTmpKey = config('custom.cookie.logged_tmp');
        if (Cookie::get($loggedTmpKey) != null) return true;

        return self::S_FORBIDDEN;
    }


    /**
     * 验证用户是否有权限
     * 引导程序
     */
    public function handle(Request $request, Closure $next) {

        // 没登录，返回
        if (!CheckLogin::handle()) {
        
            return response()->json(ErrMsg::notLogin());

        }

        // 已登录，拉取用户数据
        $userInfo = PullUserInfo::handle();

        // 是否为管理员，检查是否二次验证过
        $isAdmin = self::isAdmin($userInfo);

        if (\is_string($isAdmin)) {

            $res = ErrMsg::notSecVerify();
            return \response()->json($res);
        }

        return $next($request);
    }
}
