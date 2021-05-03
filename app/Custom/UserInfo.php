<?php

namespace App\Custom\UserInfo;

use App\Custom\PullUserInfo\PullUserInfo;

class UserInfo
{
    
    private static function init () {

        PullUserInfo::handle();
    }

    public static function id () {
        
        self::init();
        $sid = config('custom.session.user_info');
        $data = session()->get($sid);

        return $data['id'];
    }


    public static function type () {
        
        self::init();
        $sid = config('custom.session.user_info');
        $data = session()->get($sid);

        return $data['type'];
    }
}
