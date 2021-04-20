<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Session;

use App\Custom\Common\CustomCommon;

class SessionController extends Controller
{
    
    /**
     * 登出，删除tgc
     */
    public function logout (Request $request) {

        // 获取要切换的sid和原来的sid
        $session_id = $request->session_id;
        $oriSid = Session::getId();

        // 切换session，删掉tgc
        Session::setId($session_id);
        Session::forget('tgc');

        // 换回原来的sid
        Session::setId($oriSid);

        // 返回
        return CustomCommon::makeSuccRes();
    }

}
