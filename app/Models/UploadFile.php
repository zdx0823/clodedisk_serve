<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadFile extends Model
{
    use HasFactory;

    // 指定表名
    protected $table = 'upload_file';

    // 启用软删除
    use SoftDeletes;

    // 默认的时间名
    const CREATED_AT = 'ctime';
    const UPDATED_AT = 'mtime';
    const DELETED_AT = 'dtime';

    // 使用数字时间戳
    protected $dateFormat = 'U';
}
