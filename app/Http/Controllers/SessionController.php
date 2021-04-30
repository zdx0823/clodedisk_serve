<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Cookie;
use Session;

use App\Custom\Common\CustomCommon;
use App\Custom\CheckLogin\CheckLogin;
use App\Custom\CheckSt\CheckSt;
use App\Custom\CheckLoggedToken\CheckLoggedToken;

class SessionController extends Controller
{

    private const S_LOGOUT_FAIL = '登出失败，请稍后重试';
    private const S_LOGOUT_SUCC = '登出成功';
    private const S_SEND_SUCC = '发送成功';
    private const S_CONFIRM_ERR = '验证码错误或已失效';
    private const S_CONFIRM_SUCC = '验证成功';
    
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


    /**
     * 发送二次鉴权验证码
     */
    public function sendCode (Request $request) {

        $userSid = config('custom.session.user_info');
        $userInfo = \session()->get($userSid);
        $email = $userInfo['email'];

        $res = CheckLoggedToken::sendCode($email);

        if ($res !== true) return CustomCommon::makeErrRes($res);

        return CustomCommon::makeSuccRes([], self::S_SEND_SUCC);
    }


    /**
     * 核实验证码
     */
    public function confirmCode (Request $request) {

        $res = CheckLoggedToken::checkCode($request->code);

        if (!$res) return CustomCommon::makeErrRes(self::S_CONFIRM_ERR);

        return CustomCommon::makeSuccRes([], self::S_CONFIRM_SUCC);
    }


    /**
     * 是否需要二次验证
     */
    public function isNeedConfirm (Request $request) {

        $userSid = \config('custom.session.user_info');
        $userInfo = session()->get($userSid);

        $res = true;

        if ($userInfo['isAdmin'] === false) {
            
            $res = false;
        } else {

            $res = !CheckLoggedToken::hasToken();
        }

        return CustomCommon::makeSuccRes([
            'isNeed' => $res
        ]);
    }
}
