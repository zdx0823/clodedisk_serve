<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\UploadFile;

use App\Custom\Common\CustomCommon;

class ResourcesController extends Controller
{

    /**
     * 获取首页轮播图数据
     */
    public function slide () {

        $data = UploadFile::slide();
        foreach ($data as &$list) {
            
            for ($i = 0, $len = count($list); $i < $len; $i++) { 
                
                $name = $list[$i];
                $list[$i] = route('img', $name);
            }
        }

        return Customcommon::makeSuccRes($data);
    }
}
