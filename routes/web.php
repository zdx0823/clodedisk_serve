<?php
use App\Http\Controllers;

Route::prefix('/api/clodedisk')
  ->middleware([
        'authApi',
        'checkParams',
    ])
  ->group(function () {

  // 获取文件，文件夹列表
  Route::get('/list', 'DiskController@list')->name('list');
  
  // 新建文件夹
  Route::post('/folder', 'DiskController@storeFolder')->name('storeFolder');
  // 上传文件
  Route::post('/upload', 'DiskController@upload')->name('upload');
  
  // 修改文件名
  Route::put('/file/name', 'DiskController@updateFileName')->name('updateFileName');
  // 修改文件夹名
  Route::put('/folder/name', 'DiskController@updateFolderName')->name('updateFolderName');
  // 复制，剪切文件或文件夹
  Route::put('/resource/copy', 'DiskController@copyResource')->name('copyResource');
  Route::put('/resource/cut', 'DiskController@cutResource')->name('cutResource');
  
  // 删除文件或文件夹
  Route::delete('/', 'DiskController@destroy')->name('destroy');

});


// 静态页面
Route::get('/', 'StaticPageController@index')->name('indexPage');

// 登出
Route::post('/logout', 'SessionController@logout')->name('logout');

// SSO登出
Route::post('/logout/sso', 'SessionController@ssoLogout')->name('ssoLogout');

Route::get('/test', 'SessionController@test');

// 登录相关
Route::prefix('/login')->middleware(['checkParams'])->group(function () {

  Route::post('/check_st', 'SessionController@checkSt')->name('login_checkSt');
  Route::post('/check_login', 'SessionController@checkLogin')->name('login_checkLogin');

  // 发送验证码
  Route::post('/confirm/send_code', 'SessionController@sendCode')
    ->middleware('pullUserInfo')
    ->name('login_sendCode');
    
  // 核实验证码
  Route::post('/confirm/confirm', 'SessionController@confirmCode')
    ->middleware('pullUserInfo')
    ->name('login_confirmCode');

});

// 后备路由
Route::any('/{any}', function () {
  return [
    'status' => -1,
    'msg' => 'api错误',
    'data' => []
  ];
})->where('any', '.*');