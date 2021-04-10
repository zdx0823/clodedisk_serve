<?php
use App\Http\Controllers;

Route::prefix('/api/clodedisk')->middleware(['checkParams'])->group(function () {

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
  Route::put('/resource/copy', 'DiskController@changeResource')->name('changeResource');
  Route::put('/resource/cut', 'DiskController@changeResource')->name('changeResource');
  
  // 删除文件或文件夹
  Route::delete('/', 'DiskController@destroy')->name('destroy');

});

// 后备路由
Route::any('/{any}', function () {
  return [
    'status' => -1,
    'msg' => 'api错误',
    'data' => []
  ];
})->where('any', '.*');