<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadFolder extends Model
{
    use HasFactory;

    // 启用软删除
    use SoftDeletes;

    protected $table = 'upload_folder';


    // 默认的时间名
    const CREATED_AT = 'ctime';
    const UPDATED_AT = 'mtime';
    const DELETED_AT = 'dtime';

    // 使用数字时间戳
    protected $dateFormat = 'U';

}
