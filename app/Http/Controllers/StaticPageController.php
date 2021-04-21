<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Custom\CheckLogin\CheckLogin;

class StaticPageController extends Controller
{
    
    public function index (Request $request) {

        // 未登录
        if (!CheckLogin::handle()) {

        }

        return view('index');
    }

}
