<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadFileExtend extends Model
{
    use HasFactory;

    protected $table = 'upload_file_extend';
    public $timestamps = false;

    // 允许批量插入
    protected $fillable = ['size', 'ext', 'file_id'];

    public function file () {
        return $this->belongsTo('App\Models\UploadFile', 'file_id');
    }
}
