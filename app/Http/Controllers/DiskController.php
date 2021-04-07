<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DiskController extends Controller
{
    // 文件，文件夹列表
    public function show (Request $request) {
        return 'show';
    }

    // 新建文件夹
    public function storeFolder (Request $request) {
        return 'storeFolder';
    }

    // 上传文件
    public function upload (Request $request) {
        return 'upload';
    }

    // 修改文件名
    public function updateFileName (Request $request) {
        return 'updateFileName';
    }
    
    // 修改文件夹名
    public function updateFolderName (Request $request) {
        return 'updateFolderName';
    }
    
    // 复制，剪切文件或文件夹
    public function changeResource (Request $request) {
        return 'changeResource';
    }
    
    // 删除文件或文件夹
    public function destroy (Request $request) {
        return 'destroy';
    }
}
