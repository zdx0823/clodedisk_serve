<?php

namespace App\Http\Controllers;

use App\Models\UploadFolder;
use App\Models\UploadFile;
use App\Models\FolderShared;
use App\Models\FileShared;

use App\Custom\Common\CustomCommon;
use App\Custom\UserInfo\UserInfo;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\diskController\CommonController as DISKCommon;


/**
 * 鉴定用户操作的数据是否属于它本人的
 */
class IsChangeable {


    public static function update ($request) {

        $uid = UserInfo::id();

        $type = $request->type;
        $id = $request->id;

        if ($type === 'folder') {

            $ins = UploadFolder::find($id);
            if ($ins == null) return false;
            if ($ins->uid !== $uid) return false;
        } else {

            $ins = UploadFile::find($id);
            if ($ins == null) return false;
            $folder = UploadFolder::find($ins->fid);
            if ($folder->uid !== $uid) return false;
        }

        return true;
    }


    public static function item ($request) {

        $uid = UserInfo::id();
        $ins = UploadFolder::find($request->fid);

        if ($ins == null) return false;
        if ($uid !== $ins->uid) return false;

        return true;
    }
}


class SharedController extends Controller
{

    /**
     * 获取所有用户共有的顶层id
     * 管理员目录的顶层目录fid是null
     * 其他用户的顶层目录fid是2
     */
    private static function allBaseId () {

        if (UserInfo::type() === 'admin') return null;

        return 2;
    }

    
    /**
     * 设置文件夹共享状态
     * 期望接收2个值，要共享的文件夹id，和状态值
     * status: 1 不共享， 2共享
     */
    public function update (Request $request) {

        if (!IsChangeable::update($request)) {
            return CustomCommon::makeErrRes('无法共享，您无权操作此文件夹');
        }

        $id = $request->id;
        $type = $request->type;
        $status = $request->status;

        if ($type === 'folder') {
            
            if ($status === 1) {

                FolderShared::where('fid', $id)->delete();
            } else {
                
                FolderShared::updateOrInsert(
                    ['fid' => $id],
                    ['ctime' => time()]
                );
            }
        } else {
            
            if ($status === 1) {

                FileShared::where('file_id', $id)->delete();
            } else {
                
                FileShared::updateOrInsert(
                    ['file_id' => $id],
                    ['ctime' => time()]
                );
            }
        }

        return CustomCommon::makeSuccRes([], '分享成功');
    }


    /**
     * 向上判断祖先文件夹是否有被共享
     * 
     * 返回布尔值
     */
    private static function isFidShared ($fid) {

        // 向上寻找祖先文件夹
        // 1. 找该用户的基本id
        $uid = UserInfo::id();
        $baseId = UploadFolder::where('uid', $uid)
            ->where('fid', self::allBaseId())
            ->first()
            ->id;

        $res = false;
        $curFid = $fid;
        do {

            $sharedIns = FolderShared::where('fid', $curFid)->first();

            if ($sharedIns !== null) {
                $res = true;
                break;
            }

            $folder = UploadFolder::where('id', $curFid)->first();
            if ($folder == null) break;
            $curFid = $folder->fid;

        } while ($curFid !== $baseId);

        return $res;
    }


    /**
     * 获取共享文件夹里的数据
     */
    public function item (Request $request) {

        $fid = $request->fid;

        $page = $request->input('page', 1);
        $pagesize = $request->input('pagesize', 10);
        $order = $request->input('order', 'desc');

        $offset = ($page - 1) * $pagesize;
        $limit = $pagesize;

        // 即不是文件夹主人，文件夹又没有被共享，无法查看
        if (
            !IsChangeable::item($request) &&
            !self::isFidShared($fid)
        ) {

            return CustomCommon::makeErrRes('您无权查看此文件夹');
        }

        return DISKCommon::listByFidData(compact(
            'fid',
            'offset',
            'limit',
            'order'
        ));
    }
}
