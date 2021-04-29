<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Custom\CheckLogin\CheckLogin;

class StaticPageController extends Controller
{
    
    public function index (Request $request) {

        return view('index');
    }

}
