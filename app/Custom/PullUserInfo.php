<?php

namespace App\Custom\PullUserInfo;

use Cookie;
use Session;

use App\Custom\Common\CustomCommon;


/**
 * 拉取用户数据，
 * 成功向session写入数据，并返回true
 * 失败返回false
 */
class PullUserInfo {

    /**
     * 向SSO拉取用户信息
     * 需要tgc作为参数发送请求
     * 成功请求到数据将赋值到session，session的key是custom.session.user_info，值就是数据
     * 
     * 返回false或session数据
     */
    private static function pullUserInfo () {

        $tgc = Cookie::get('tgc');
        $sessionKey = config('custom.session.user_info');

        if (session()->has($sessionKey)) {
            
            return \session()->get($sessionKey);
        };

        // 发送请求
        $url = config('custom.sso.user_info');
        $url = "$url?tgc=$tgc";
        $data = CustomCommon::client('GET', $url);

        // 请求失败，返回失败
        if ($data['status'] === -1) return false;

        $userInfo = $data['data'];

        $userSessionKey = config('custom.session.user_info');

        $data = [
            'id' => $userInfo['id'],
            'username' => $userInfo['username'],
            'email' => $userInfo['email'],
            'isAdmin' => $userInfo['isAdmin'],
        ];

        session([ $userSessionKey => $data ]);

        return $data;
    }


    public static function handle () {
        return self::pullUserInfo();
    }

}