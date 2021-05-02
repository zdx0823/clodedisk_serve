<?php

namespace App\Http\Controllers\diskController;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Models\UploadFolder;

class CommonController extends Controller
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


    /**
     * 取出所有后代文件夹
     * $folderIdArr 文件夹id数组
     * 
     * 返回3个数组，offspring只有后代，target第一层文件夹，all所有文件夹
     * 每一个都是二维数组，形如：[ ['id' => 5, 'fid' => 1, 'name' => '文件夹'] ]
     */
    public static function getOffspringFolder ($folderIdArr) {

        $folderData = [];  // 汇总数组，存放自身和所有后代的文件夹数据

        // 递归循环取出所有后代
        $currentIdList = $folderIdArr;  // 当前循环的fid列表
        do {
            
            $arr = UploadFolder::select(['id', 'fid', 'name'])
                ->whereIn('fid', $currentIdList)
                ->get()
                ->toArray();

            $currentIdList = array_column($arr, 'id');
            $folderData = array_merge($folderData, $arr);

        } while (count($currentIdList) > 0);

        // 取出自身数据
        $arr = UploadFolder::select(['id', 'fid', 'name'])
            ->whereIn('id', $folderIdArr)
            ->get()
            ->toArray();

        return [
            'offspring' => $folderData,
            'target' => $arr,
            'all' => array_merge($folderData, $arr),
        ];
    }
}
