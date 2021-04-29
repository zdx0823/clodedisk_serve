<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Cookie;
use Session;

use App\Custom\Common\CustomCommon;
use App\Custom\CheckLogin\CheckLogin;
use App\Custom\CheckSt\CheckSt;

class SessionController extends Controller
{

    private const S_LOGOUT_FAIL = '登出失败，请稍后重试';
    private const S_LOGOUT_SUCC = '登出成功';
    
    /**
     * 登出接口，供SSO使用
     * 1. 需求参数：tgc, session_id
     * 2. 切换session, 删除tgc, 切换回原来session
     */
    public function ssoLogout (Request $request) {

        // 登出
        CustomCommon::ssoLogout($request);
        
        // 返回
        return CustomCommon::makeSuccRes();

    }


    /**
     * 1. 登出自己
     * 2. 要求SSO登出其他子系统
     */
    public function logout (Request $request) {

        $tgc = Cookie::get('tgc');

        // 删除自己的tgc
        Cookie::queue('tgc', 'null', -99999);
        Session::forget('tgc');

        return CustomCommon::makeSuccRes(compact('tgc'));
    }


    public function test (Request $request) {
        return '测试';
    }


    /**
     * 检查是否登录
     */
    public function checkLogin (Request $request) {

        if (!CheckLogin::handle()) return CustomCommon::makeErrRes('未登录');

        return CustomCommon::makeSuccRes([], '已登录');
    }


    /**
     * 检查ST是否可用
     */
    public function checkSt (Request $request) {

        if (!CheckSt::handle($request)) return CustomCommon::makeErrRes('未登录');

        return CustomCommon::makeSuccRes([], '已登录');
    }
}
