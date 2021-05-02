<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileShared extends Model
{
    use HasFactory;

    public $timestamps = false;

    // 允许批量插入
    protected $fillable = ['file_id', 'ctime'];
}
