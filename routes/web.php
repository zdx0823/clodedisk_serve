<?php


Route::namespace('clodedisk')->prefix('/api/clodedisk')->group(function () {

  Route::get('/{fid}', 'DiskController@show');  // 获取文件，文件夹列表
  
  Route::Post('/folder', 'DiskController@storeFolder');  // 新建文件夹
  Route::Post('/upload', 'DiskController@upload');  // 上传文件
  
  Route::Put('/file/name', 'DiskController@updateFileName');  // 修改文件名
  Route::Put('/folder/name', 'DiskController@updateFolderName');  // 修改文件夹名
  Route::Put('/resource/location', 'DiskController@changeResource');  // 复制，剪切文件或文件夹
  
  Route::Delete('/', 'DiskController@destroy'); // 删除文件或文件夹

});