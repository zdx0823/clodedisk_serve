<?php

namespace App\Http\Controllers\clodedisk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DiskController extends Controller
{
    // 文件，文件夹列表
    public function show (Request $request) {}

    // 新建文件夹
    public function storeFolder (Request $request) {}

    // 上传文件
    public function upload (Request $request) {}

    // 修改文件名
    public function updateFileName (Request $request) {}
    
    // 修改文件夹名
    public function updateFolderName (Request $request) {}
    
    // 复制，剪切文件或文件夹
    public function changeResource (Request $request) {}
    
    // 删除文件或文件夹
    public function destroy (Request $request) {}
}
