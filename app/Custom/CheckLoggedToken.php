<?php

namespace App\Custom\CheckLoggedToken;

use Cookie;
use Session;
use Mail;

use App\Custom\Common\CustomCommon;


class CheckLoggedToken
{

    private const CODE_TIMEOUT = 60;
    public const SEND_CODE_ERR = '验证码已发送，请勿重复操作';
    
    /**
     * 发送邮件
     * $params:  email, view, data
     * 
     * 无返回值
     */
    private static function send ($params) {

        [
            'email' => $email,
            'view' => $view,
            'data' => $data,
        ] = $params;

        $name = 'JD';
        $to = $email;
        $subject = "$name 验证码";

        Mail::send($view, $data, function ($message) use ($name, $to, $subject) {
            $message->to($to)->subject($subject);
        });
    }


    /**
     * 生成6位数的数字验证码，并保存到用户数据session中
     * 返回验证码
     */
    private static function buildCode () {
        
        $userSid = config('custom.session.user_info');
        $userInfo = session()->get($userSid);
        
        $code = \random_int(100000, 999999);
        $userInfo['code'] = [
            'code' => $code,
            'timeout' => time() + self::CODE_TIMEOUT,
        ];

        session([$userSid => $userInfo]);

        return $code;
    }


    /**
     * 检查是否有临时token
     * cookie不存在，session没有相同项，返回false
     * 否则返回true
     */
    public static function hasToken () {

        // 已登录，是否有临时登录凭证
        $cid = \config('custom.cookie.logged_tmp');
        $sid = config('custom.session.user_info');
        $userInfo = session()->get($sid);

        if (Cookie::get($cid) == null) return false;
        if (!array_key_exists($cid, $userInfo)) return false;
        if (Cookie::get($cid) !== $userInfo[$cid]) return false;

        return true;
    }


    /**
     * 发送有效期1分钟的6位数验证码到邮箱
     * $email 邮箱
     * 1. 如已发送，1分钟内将不再发送，并返回提示文字
     * 
     * 正常返回true，异常返回提示文字
     */
    public static function sendCode ($email) {

        $userSid = config('custom.session.user_info');
        $userInfo = session()->get($userSid);

        // 是否已发送
        if (
            isset($userInfo['code']) &&
            time() < $userInfo['code']['timeout']
        ) {
            return self::SEND_CODE_ERR;
        }

        $code = self::buildCode();
        $data = compact('code');
        $view = 'email.code';

        self::send(compact(
            'email',
            'data',
            'view'
        ));

        return true;
    }


    // 生成临时token
    private static function setTmpToken () {

        $token = CustomCommon::build_token();

        $cid = config('custom.cookie.logged_tmp');
        Cookie::queue($cid, $token);

        $sid = config('custom.session.user_info');
        $userInfo = session($sid);
        $userInfo[$cid] = $token;
        session([
            $sid => $userInfo
        ]);
    }


    // 删掉code的session
    private static function delCodeSession () {

        $userSid = config('custom.session.user_info');
        $userInfo = session()->get($userSid);
        $userInfo['code'] = null;
        session([$userSid => $userInfo]);
    }


    /**
     * 验证邮箱验证码，成功将生成临时token写入cookie
     */
    public static function checkCode ($code) {
        
        $userSid = config('custom.session.user_info');
        $userInfo = session()->get($userSid);

        // 验证码存在，且未超时
        if (isset($userInfo['code']) && time() < $userInfo['code']['timeout']) {
            
            // 验证码不正确
            if (intval(trim($code)) !== $userInfo['code']['code']) {
                return false;
            }

            self::setTmpToken();
            self::delCodeSession();
            return true;
        }

        return false;
    }

}
