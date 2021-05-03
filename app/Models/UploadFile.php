<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

use App\Models\UploadFolder;

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


    /**
     * 映射到轮播图数据
     * 1. 获取管理员轮播图下的目录id
     * 2. 获取这些id的图片，目录名为键，目录下的文件为数组返回
     */
    public static function slide () {
        
        // 管理员顶层文件夹id
        $rootBaseId = UploadFolder::where('fid', '=', null)
            ->first()
            ->id;

        // 找到“轮播图”文件夹
        $slideBaseId = UploadFolder::where('fid', $rootBaseId)
            ->where('name', '轮播图')
            ->first()
            ->id;

        $sliderIns = UploadFolder::where('fid', $slideBaseId)->get();
        $data = [];
        foreach ($sliderIns as $ins) {
            
            $res = UploadFile::where('fid', $ins->id)
                ->get()
                ->toArray();

            $nameList = array_column($res, 'name');
            $data[$ins->name] = $nameList;
        }

        return $data;
    }
}
