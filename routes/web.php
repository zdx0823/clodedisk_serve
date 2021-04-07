<?php
use App\Http\Controllers;

Route::prefix('/api/clodedisk')->middleware(['checkParams'])->group(function () {

  // 获取文件，文件夹列表
  Route::get('/{fid}', 'DiskController@show')->name('show');
  
  // 新建文件夹
  Route::Post('/folder', 'DiskController@storeFolder')->name('storeFolder');
  // 上传文件
  Route::Post('/upload', 'DiskController@upload')->name('upload');
  
  // 修改文件名
  Route::Put('/file/name', 'DiskController@updateFileName')->name('updateFileName');
  // 修改文件夹名
  Route::Put('/folder/name', 'DiskController@updateFolderName')->name('updateFolderName');
  // 复制，剪切文件或文件夹
  Route::Put('/resource/location', 'DiskController@changeResource')->name('changeResource');
  
  // 删除文件或文件夹
  Route::Delete('/', 'DiskController@destroy')->name('destroy');

});

// 后备路由
Route::any('/{any}', function () {
  return [
    'status' => -1,
    'msg' => 'api错误',
    'data' => []
  ];
})->where('any', '.*');