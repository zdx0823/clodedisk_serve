<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Cookie;

use Illuminate\Http\Request;

use App\Custom\Common\CustomCommon;
use App\Custom\CheckLogin\CheckLogin;
use App\Custom\CheckSt\CheckSt;

/**
 * api鉴权，检查用户是否有权限
 * 1. 是否已登录
 * 2. 拉取用户信息存储到session
 * 3. 未登录返回重定向的url
 */
class AuthApi {


    /**
     * 生成错误的返回结果
     * 生成一个重定向的url，返回给前端重定向使用
     */
    private static function makeErrRes ($request) {

        $SSO = config('custom.sso.login');

        // json返回值
        $res = CustomCommon::makeErrRes(
            '未登录，请登录后操作',
            [],
            [ 'sso' => $SSO ],
            -2,
        );

        return $res;
    }


    /**
     * 向SSO拉取用户信息
     * 需要tgc作为参数发送请求
     * 成功请求到数据将赋值到session，session的key是tgc，值就是数据
     */
    private static function pullUserInfo () {

        $tgc = Cookie::get('tgc');

        if (session()->has($tgc)) return;

        // 发送请求
        $url = config('custom.sso.user_info');
        $data = CustomCommon::client('GET', $url, [
            'form_params' => compact('tgc')
        ]);

        // 请求失败，静默返回
        if ($data['status'] === -1) return;

        $userInfo = $data['data'];

        session([ $tgc => $userInfo ]);
    }


    /**
     * 验证用户是否有权限
     * 引导程序
     */
    public function handle(Request $request, Closure $next) {

        // 没登录，返回
        if (!CheckLogin::handle()) {
        
            return response()->json(self::makeErrRes($request));

        }


        // 已登录，拉取用户数据
        self::pullUserInfo();

        return $next($request);
    }
}
