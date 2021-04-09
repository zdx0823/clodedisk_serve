<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

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

    // 自定义字段
    protected $appends = ['type'];

    // 使用数字时间戳
    protected $dateFormat = 'U';

    // 允许批量插入
    protected $fillable = ['fid', 'name', 'alias'];

    public function getCtimeAttribute ($value) {
        return Carbon::create($value)->toDateTimeString();
    }

    public function getMtimeAttribute ($value) {
        return Carbon::create($value)->toDateTimeString();
    }

    public function getTypeAttribute () {
        return 'file';
    }

    public function folder () {
        return $this->belongTo('App\Models\UploadFolder', 'fid');
    }

    public function extend_info () {
        return $this->hasOne('App\Models\UploadFileExtend', 'file_id');
    }
}
