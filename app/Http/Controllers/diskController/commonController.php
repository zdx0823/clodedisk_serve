<?php

namespace App\Http\Controllers\diskController;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class commonController extends Controller
{
    /**
     * 插入一条文件记录，
     * $params
     *      name, alias, fid, size, ext
     * 
     * $isUseTrans 是否开启事务，布尔值，如果为false，插入的记录置为非删除状态 
     * 
     * 返回，执行失败返回null，成功返回插入的文件id
     */
    public static function insertFileToDB ($params, $isUseTrans = false) {
        
        [
            'name' => $name,
            'alias' => $alias,
            'fid' => $fid,
            'size' => $size,
            'ext' => $ext,
        ] = $params;

        $insertId = null;

        $ctime = $mtime = $dtime = time();

        if ($isUseTrans) {
            DB::beginTransaction();
        }

        // 插入文件
        try {

            $file_id = DB::table('upload_file')->insertGetId(compact(
                'name',
                'alias',
                'fid',
                'ctime',
                'mtime',
                'dtime'
            ));

            // 插入拓展信息
            DB::table('upload_file_extend')->insert(compact(
                'size',
                'ext',
                'file_id',
            ));

            $insertId = $file_id;

        } catch (\Throwable $th) {
            $insertId = null;
        }

        // 失败回滚，成功提交
        if ($isUseTrans) {

            if ($insertId) {
                DB::commit();

                // 把该文件置非删除状态
                DB::table('upload_file')
                    ->where('id', $file_id)
                    ->update(['dtime' => null]);

            } else {
                DB::rollback();
            }

        }

        return $insertId;
    }
}
