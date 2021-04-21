<?php

namespace App\Custom\CheckSt;

use Cookie;
use Session;

use App\Custom\Common\CustomCommon;


/**
 * url是否存在ST的query参数，有则向SSO验证
 * 1. 可用，向cookie和session写入tgc值
 * 2. 不可用，结束，返回false
 * 
 * 结果返回布尔值
 */
class CheckSt {


    /**
     * 向SSO服务器发送验证ST请求
     * 验证失败返回false，成功返回数组，数组包括tgc，和tgc的过期时间
     */
    private static function sendRequestToSSO ($st) {

        $url = config('custom.sso.check_st');
        $session_id = Session::getId();

        $data = Customcommon::client('POST', $url, [
            'form_params' => compact('st', 'session_id')
        ]);

        if ($data['status'] === -1) return false;

        // 解密tgc
        $tgc = $data['data']['tgc'];
        $timeout = $data['data']['timeout'];

        return compact('tgc', 'timeout');
    }


    /**
     * 查看请求参数中是否存在ST，存在ST则向SSO进行验证
     * 验证成功将tgc存入cookie和session
     * 
     * 结果返回布尔值
     */
    public static function handle ($request) {

        $st = $request->st;

        if ($st == null) return false;

        $res = self::sendRequestToSSO($st);

        if ($res === false) return false;

        $tgc = $res['tgc'];
        $timeout = $res['timeout'];

        Cookie::queue('tgc', $tgc, $timeout);
        session(\compact('tgc'));

        return true;
    }

}
