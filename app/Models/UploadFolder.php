<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class UploadFolder extends Model
{
    use HasFactory;

    // 启用软删除
    use SoftDeletes;

    protected $table = 'upload_folder';

    protected $fillable = ['uid', 'uid_type', 'fid', 'name'];

    // 默认的时间名
    const CREATED_AT = 'ctime';
    const UPDATED_AT = 'mtime';
    const DELETED_AT = 'dtime';

    // 使用数字时间戳
    protected $dateFormat = 'U';

    // 自定义字段
    protected $appends = ['type'];

    public function files () {
        return $this->hasMany('App\Models\UploadFile', 'fid');
    }

    public function getCtimeAttribute ($value) {
        return Carbon::create($value)->toDateTimeString();
    }

    public function getMtimeAttribute ($value) {
        return Carbon::create($value)->toDateTimeString();
    }

    public function getTypeAttribute () {
        return 'folder';
    }
}
