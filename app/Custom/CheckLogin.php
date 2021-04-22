<?php

namespace App\Custom\CheckLogin;

use Cookie;
use Session;

use App\Custom\Common\CustomCommon;


/**
 * 判断用户是否登录
 * 1. 首次打开浏览器，向SSO验证，返回布尔值
 * 2. cookie和session的tgc不同步，向SSO验证，成功，同步两则的值，失败删掉两者的值，返回布尔值
 * 
 * 返回布尔值
 */
class CheckLogin
{

    private const TMP_COOKIE_NAME = 'TMP_COOKIE_ctm';

    /**
     * 向SSO服务器发起请求，验证TGC是否有效
     * 同时发送当前的session_id，让SSO更新
     */
    private static function checkTgc ($tgc) {

        $url = config('custom.sso.check_tgc');
        $data = CustomCommon::client('POST', $url, [
            'form_params' => compact('tgc')
        ]);

        return ($data['status'] == 1);
    }


    /**
     * 删掉cookie和session的tgc
     */
    private static function delTgc () {

        Cookie::queue(Cookie::forget('tgc'));
        Session::forget('tgc');

    }


    /**
     * 判断是否为第一次打开浏览器，第一次进入该网页
     * 如果是第一次，需到SSO验证 tgc 是否有效，
     * 如果tgc无效，则将tgc的cookie和session都删除
     */
    private static function firstRequest () {

        if (Cookie::get(self::TMP_COOKIE_NAME)) return;

        // 第一次进入网页，判断tgc是否还有效
        $tgc = Cookie::get('tgc');

        // tgc不存在或tgc已失效，删掉cookie和session的tgc
        if (!$tgc || !self::checkTgc($tgc)) {
            self::delTgc();
        }

        // 生成临时cookie
        Cookie::queue(self::TMP_COOKIE_NAME, time());

    }


    /**
     * cookie和session里的tgc是否相等
     */
    private static function isHasSameTgc () {

        $cookie = Cookie::get('tgc');
        $session = Session::get('tgc');

        if (!$cookie || !$session) return false;

        return $cookie === $session;
    }


    /**
     * 判断是否登录，根据cookie和session的tgc是否相等判断
     * 1. 不相等，可能是session过期或session的tgc被SSO发起的请求删掉了
     * 2. cookie的tgc不存在，则未登录
     * 
     * 返回布尔值
     */
    public static function handle() {

        // 是否为第一次进网页，做一些自检工作
        self::firstRequest();

        $tgc = Cookie::get('tgc');
        
        // tgc不存在，返回
        if (!$tgc) return false;

        // cookie和session的tgc不相等
        if (!self::isHasSameTgc()) {

            // 查看数据库，tgc是否还可用
            $checkTgcRes = self::checkTgc($tgc);

            // 检查结果，tgc不可用，删掉tgc，返回
            if (!$checkTgcRes) {
                self::delTgc();
                return false;
            };

            // tgc还能用，更新session的值
            session(['tgc' => $tgc]);

            // 结束中间件
            return true;
        }

        return true;
    }
}
