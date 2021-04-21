<?php

namespace App\Http\Controllers\diskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UploadFolder;
use App\Models\UploadFile;
use App\Models\UploadFileExtend;
use App\Custom\Common\CustomCommon;
use Illuminate\Support\Facades\DB;

class PasetController extends Controller
{
    
    /**
     * 判断要复制的文件和文件夹是否来自同一个父级
     * $fileIdArr 文件id数组，$folderIdArr 文件夹id数组
     * 
     * 返回布尔值
     */
    private static function isFromSameFid ($fileIdArr, $folderIdArr) {

        // 判断文件
        $fileFid = -1;
        if (count($fileIdArr) > 0) {

            // 查出这些文件的fid，取出fid数组
            $fidList = UploadFile::select('fid')
            ->whereIn('id', $fileIdArr)
            ->pluck('fid')
            ->toArray();

            // 去重看有没有重复，长度大于1表示有重复，即有文件来自另一个文件夹下
            if (count(array_unique($fidList)) > 1) {
                return false;
            }

            // 没有文件夹不用判断，直接返回
            if ($folderIdArr == null) return true;

            $fileFid = $fidList[0];
        }
    

        // 判断文件夹
        $folderFid = -1;
        if (count($folderIdArr) > 0) {

            $fidList = UploadFolder::select('fid')
                ->whereIn('id', $folderIdArr)
                ->pluck('fid')
                ->toArray();

            // 去重看有没有重复，长度大于1表示有重复，即有文件来自另一个文件夹下
            if (count(array_unique($fidList)) > 1) {
                return false;
            }

            // 没有文件不用判断，直接返回
            if ($fileIdArr == null) return true;

            $folderFid = $fidList[0];
        }
    
        
        if ($fileFid !== $folderFid) {
            return false;
        } else {
            return true;
        }
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


    /**
     * 取出类似的数据，$params为 $model, $distId, $nameList, $nameField
     *      $model: 模型实例，
     *      $distId：目的地文件夹id，
     *      $nameList：要输入的名称列表，
     *      $nameFieldId要对比的字段，
     *      $type 模型类型，file 或 folder。根据这个参数判断后缀，如果为文件，会取出后缀后再执行相应的逻辑
     * 
     * 返回二维数组，
     *      形如：[ [id => 3, fid => 1, name => '文件夹'] ]
     *      或：[ [id => 3, fid => 1, name => '图片.jpg'] ]
     */
    private static function getSimilarName ($params) {

        [
            'model' => $model,
            'distId' => $distId,
            'nameList' => $nameList,
            'nameField' => $nameField,
            'type' => $type,
        ] = $params;

        // 是否为文件
        $isFile = $type === 'file' ? true : false;

        // 合成正则
        $regexpArr = [];
        foreach ($nameList as $name) {
            
            // 分割成两部分
            [
                'firstVal' => $firstVal,
                'lastVal' => $lastVal,
                'ext' => $ext,
            ] = CustomCommon::explodeName($name, $isFile);

            // 如果firstVal不存在，则用lastVal做正则条件
            $firstVal = $firstVal == null ? $lastVal : $firstVal;
            $firstVal = CustomCommon::escapeSQL($firstVal);

            array_push(
                $regexpArr,
                "$nameField REGEXP '^$firstVal(\\\\([0-9]+\\\\)){0,1}$ext$'"
            );

        }

        // 合并成字符串
        $regexp = implode(' OR ', $regexpArr);

        // 查询
        $similarNameList = $model->select(['id', 'fid', $nameField])
            ->where('fid', $distId)
            ->whereRaw("($regexp)")
            ->get()
            ->toArray();

        return $similarNameList;
    }


    /**
     * 生成一个可用的名称
     * 给重名项递增一个数字，
     * 文件夹：假设重复项为：['文件夹(1)', '文件夹(2)'] 修改后 ['文件夹(3)', '文件夹(4)']
     * 文件：['文件夹(1).jpg', '文件夹(2).jpg'] 修改后 ['文件夹(3).jpg', '文件夹(4).jpg']
     * 
     * $params:
     *      targetName: 原完整名称
     *      firstVal: 不带小括号和后缀前面的部分
     *      currentDistList: 要比对的列表
     *      bigNumMap: firstVal对应最大后缀数字索引匹配，
     *              形如：[ '文件夹' => 2, ]，表示已存在，文件夹，文件夹(1)和文件夹(2)，还有重名需使用 "文件夹(3)"
     *      escapedPreg:  已经正则化的正则搜索字符串
     *      type: 名称类型，file或folder
     * 
     * 此方法依赖于：
     *      CustomCommon::getExtByName
     * 
     * 返回数组，bigNumMap和finalName，外部需要把bigNumMap重新赋值
     */
    protected static function buildUsableName ($params) {

        [
            'targetName' => $name,
            'firstVal' => $firstVal,
            'currentDistList' => $distNameList,
            'bigNumMap' => $bigNumMap,
            'escapedPreg' => $escapedPreg,
            'type' => $type,
        ] = $params;

        // $name是否存在与$distNameList，如果不存在则可直接用
        if (!in_array($name, $distNameList)) return [
            'finalName' => $name,
            'bigNumMap' => $bigNumMap,
        ];

        $ext = '';

        // 如果type是文件，则取出后缀
        if ($type === 'file') {
            $ext = CustomCommon::getExtByName($name);
            $ext = mb_strlen($ext) > 0 ? ('.' . $ext) : $ext;
        }


        // 取出$name的数字，没有默认0
        preg_match("/$escapedPreg(\((\d+)\)){1}$ext$/", $name, $p1);
        $nameNum = count($p1) > 0 ? intval($p1[2]) : 0;


        // 取出$distNameList的数字，组成数组
        $numList = array_map(function ($name) use ($escapedPreg, $ext) {
            
            preg_match("/$escapedPreg(\((\d+)\)){1}$ext$/", $name, $p1);
            $n = count($p1) > 0 
                ? intval($p1[2])
                : 0;
            return $n;

        }, $distNameList);

        // 降序，取出最大的数字
        rsort($numList);
        $bigNameNum = array_shift($numList);

        // 如果为0赋值成1，如果不为0，递增1
        $bigNameNum = $bigNameNum === 0 ? 1 : $bigNameNum + 1;

        // 取较大的数字
        $bigNum = max($bigNameNum, $nameNum);

        // 判断该类似名称的最大数字是否存在，存在需要再递增1
        if (isset($bigNumMap[$firstVal])) {
            $n = $bigNumMap[$firstVal] + 1;
            $bigNumMap[$firstVal] = $n;

            $bigNum = $n;
        } else {
            $bigNumMap[$firstVal] = $bigNum;
        }

        
        $finalName = "$firstVal($bigNum)$ext";
        return compact('finalName', 'bigNumMap');
    }


    /**
     * 去重名化，将重名的添加后缀，并自动递增后缀数字
     * $params
     *      distId 目的地文件夹id
     *      nameField 要比对的字段
     *      model 模型实例
     *      targetData 要比对的数据
     *      type 比对数据的类型，file或folder
     * 
     * 此方法依赖于：
     *      self::getSimilarName
     *      CustomCommon::explodeName
     *      CustomCommon::escapePreg
     *      self::buildUsableName
     * 
     * 返回修改后的$targetData
     */
    private static function deWeightNames ($params) {
        
        [
            'distId' => $distId,
            'nameField' => $nameField,
            'model' => $model,
            'targetData' => $targetData,
            'type' => $type,
        ] = $params;


        // 取出名字列
        $nameList = array_column($targetData, $nameField);

        // 找出目的文件夹下名称与$nameList相似的部分
        $similarData = self::getSimilarName(compact(
            'nameField',
            'model',
            'distId',
            'nameList',
            'type'
        ));

        $similarNameList = array_column($similarData, $nameField);  // 名字列
        $bigNumMap = [];  // 键名为 finalVal，键值为 finalVal类似名称里最大的数字
        $targetDataRes = [];  // 生成新的数组
        foreach ($targetData as $targetItem) {
            
            $targetName = $targetItem[$nameField];
            [
                'firstVal' => $firstVal,
                'lastVal' => $lastVal,
                'ext' => $ext,
            ] = CustomCommon::explodeName($targetName, $type);

            // 取出不带小括号的部分，并正则化，如果没有firstVal则lastVal作为搜索参数
            $firstVal = $firstVal == null ? $lastVal : $firstVal;
            $escapedPreg = CustomCommon::escapePreg($firstVal);

            // 取出与$firstVal类似的项
            $currentDistList = [];
            foreach ($similarNameList as $similarName) {

                preg_match("/$escapedPreg(\((\d+)\)){0,1}$ext$/", $similarName, $p1);
                if (count($p1) > 0) {
                    array_push($currentDistList, $similarName);
                }

            }
            
            // 生成一个可用的名字，修改targetItem
            $buildRes = self::buildUsableName(compact(
                'targetName',
                'firstVal',
                'currentDistList',
                'bigNumMap',
                'escapedPreg',
                'type',
            ));

            $bigNumMap = $buildRes['bigNumMap'];
            $finalName = $buildRes['finalName'];


            $targetItem[$nameField] = $finalName;

            array_push($targetDataRes, $targetItem);
        }
        
        return $targetDataRes;
    }



    /**
     * 创建文件夹，并修正文件夹之间的关系。默认为删除状态
     * $finalArr 顶层文件夹去重名后的数组
     * $distId 目的地文件夹id
     * $uid,$uid_type 辨认用户身份
     * 
     * 返回false，或 id匹配数组，键名为原文件夹id，键值为新文件夹id。该数组即文件的fidMap
     */
    private static function storeFolder ($finalArr, $distId, $uid, $uid_type) {

        $insertIds = [];

        // 按finalArr的顺序依次插入并记录id值，并置为删除状态
        // 使用DB事务，如果执行失败回滚
        DB::beginTransaction();

        $isFail = false;
        try {

            $ctime = $mtime = $dtime = time();
            foreach ($finalArr as $target) {

                $insertId = DB::table('upload_folder')->insertGetId([
                    'name' => $target['name'],
                    'uid' => $uid,
                    'uid_type' => $uid_type,
                    'ctime' => $ctime,
                    'mtime' => $mtime,
                    'dtime' => $dtime,
                ]);
                array_push($insertIds, $insertId);

            }

        } catch (\Throwable $th) {
            $isFail = true;
        }

        // 插入失败提前返回
        if ($isFail) {
            DB::rollBack();
            return false;
        }
        
        // 提交事务
        DB::commit();
        
        // 拿源数据和新数据做个id匹配
        $idMap = array_combine(
            array_column($finalArr, 'id'),
            $insertIds
        );

        // 取出源数据的fid列，并根据$idMap修改
        // 如果fid不存在与$idMap表示它是第一层文件夹，它的fid是distId
        $fidList = [];
        foreach ($finalArr as $final) {
            $fid = $final['fid'];
            if (isset($idMap[$fid])) {
                array_push($fidList, $idMap[$fid]);
            } else {
                array_push($fidList, $distId);
            }
        }

        // 做个fid的map
        $fidMap = array_combine(
            $insertIds,
            $fidList
        );

        // 更新fid值
        $ins = DB::table('upload_folder');
        foreach ($fidMap as $id => $fid) {
            $ins->where('id', $id)->update(['fid' => $fid]);
        }

        return $idMap;
    }


    /**
     * 复制文件夹，返回第一层和所有后代的id
     * $params
     *      uid, uid_type 辨认用户身份
     *      folderIdArr 顶层文件夹id数组
     *      distId  目的地文件夹id
     * 
     * 返回true，或错误提示语
     */
    private static function pasetFolder ($params) {

        [
            'uid' => $uid,
            'uid_type' => $uid_type,
            'folderIdArr' => $folderIdArr,
            'distId' => $distId,
        ] = $params;

        // 取出所有后代文件夹
        $sourceData = self::getOffspringFolder($folderIdArr);
        $allData = $sourceData['all'];
        $targetData = $sourceData['target'];

        // 取出所有id
        $allIdList = array_column($allData, 'id');

        // 看后代文件夹id是否与目标文件夹id重叠，有则表示父子关系错误，无法粘贴
        if (in_array($distId, $allIdList)) {
            return '复制失败，目标文件夹是源文件夹的子文件夹';
        }

        // 修正重复的名字，自动递增后缀数字
        $targetDataRes = self::deWeightNames([
            'distId' => $distId,
            'nameField' => 'name',
            'model' => new UploadFolder,
            'targetData' => $targetData,
            'type' => 'folder',
        ]);

        // 合并第一层数组和子代数组
        $finalArr = array_merge($sourceData['offspring'], $targetDataRes);

        // 创建文件夹
        $idMap = self::storeFolder($finalArr, $distId, $uid, $uid_type);

        // 执行失败，提前返回
        if ($idMap === false) return '复制失败，请重试';
        

        // 找出所有后代文件
        $offspringIns = UploadFile::select(['id', 'fid', 'name', 'alias'])
            ->whereIn('fid', array_column($allData, 'id'))
            ->get();

        // 复制所有后代文件
        $isPaseFileOk = self::pasetFilesByData($offspringIns, $idMap);        

        // 取出新插入文件夹的id
        $newFolderId = array_values($idMap);

        // 文件复制，失败将已插入的文件夹删除
        if (!$isPaseFileOk) {
            
            DB::table('upload_folder')->whereIn('id', $newFolderId)->delete();
            return '复制失败，请重试';

        }


        // 成功，将文件夹置为非删除状态
        DB::table('upload_folder')->whereIn('id', $newFolderId)->update(['dtime' => null]);

        return true;
    }


    /**
     * 复制文件，和复制拓展表对应的记录，默认为非删除状态
     * $filesIns  文件模型实例，是查询后的结果，即 UploadFile::xxx->xxx->get() 的返回结果
     * $fidMap  fid匹配数组，键名是原fid，键值是新的fid，用于更新插入文件的fid
     * $deWeightData  去重名化的数据，如果有，用这个来插入数据库
     * 
     * 返回布尔值，成功true，失败false
     */
    private static function pasetFilesByData ($filesIns, $fidMap, $deWeightData = null) {

        // 按顺序插入文件，保存id
        $insertIds = [];
        
        $files = $deWeightData != null
            ? $deWeightData
            : $filesIns->toArray();

        // id做键名，其他做键值，键值是数组
        $filesFlat = [];
        foreach ($files as $item) {
            $filesFlat[$item['id']] = $item;
        }

        $isCreateFileOk = true;

        // 开启事务，执行失败回滚
        DB::beginTransaction();

        // 尝试插入文件，如果SQL异常则回滚
        try {

            foreach ($filesIns as $file) {
                $fid = $file->fid;
                $name = $file->name;
                $alias = $file->alias;
                $ext = $file->extend_info->ext;
                $size = $file->extend_info->size;

                $insertId = commonController::insertFileToDB(compact(
                    'fid', 'name', 'alias', 'ext', 'size',
                ), false);

                if ($insertId == null) {
                    $isCreateFileOk = false;
                    break;
                }

                array_push($insertIds, $insertId);
            }

        } catch (\Throwable $th) {
            $isCreateFileOk = false;
        }

        // 有一个插入失败则回滚
        if (!$isCreateFileOk) {
            DB::rollback();
            return false;
        }

        // 插入成功，更新fid值和dtime
        DB::commit();
        foreach ($fidMap as $oriFid => $fid) {
            DB::table('upload_file')
                ->whereIn('id', $insertIds)
                ->where('fid', $oriFid)
                ->update([
                    'fid' => $fid,
                    'dtime' => null,
                ]);
        }

        return true;
    }


    /**
     * 复制后代文件
     * $params
     *      uid, uid_type 辨认用户身份
     *      allFid 所有的文件夹id，包括后代的
     *      distId 目的地文件夹id
     *      fidMap fid匹配数组
     * 
     * 返回布尔值
     */
    private static function pasetOffspringFile ($params) {

        [
            'uid' => $uid,
            'uid_type' => $uid_type,
            'allFid' => $allFid,
            'distId' => $distId,
            'fidMap' => $fidMap,
        ] = $params;


        // 找出所有后代文件
        $offspringIns = UploadFile::select(['id', 'fid', 'name', 'alias'])
            ->whereIn('fid', $allFid)
            ->get();

        return self::pasetFilesByData($offspringIns, $fidMap);
    }



    /**
     * 复制文件和文件夹
     * $request Request的实例，从$request->json()->all() 中获取数据使用
     * 
     * 返回true或错误提示语，执行失败由系统发出错误
     */
    public static function paset ($params) {

        $uid = 1;
        $uid_type = 3;
        [
            'idList' => $idList,
            'distId' => $distId,
        ] = $params;

        
        // 把文件和文件夹分成两个数组
        $fileIdArr = [];
        $folderIdArr = [];
        foreach ($idList as $item) {
            $id = $item['id'];
            $type = $item['type'];
            if ($type == 'file') {
                array_push($fileIdArr, $id);
            } else {
                array_push($folderIdArr, $id);
            }
        }

        
        // 判断idList是否为同一个文件夹下的文件，如果不是不允许复制
        $isDataOk = self::isFromSameFid($fileIdArr, $folderIdArr);
        if (!$isDataOk) return '数据不可用，请刷新后重试';

        // 复制文件夹
        if (count($folderIdArr) > 0) {

            $res = self::pasetFolder(compact(
                'uid',
                'uid_type',
                'folderIdArr',
                'distId',
            ));

            if ($res !== true) return $res;

        }

        // 复制文件
        if (count($fileIdArr) > 0) {
            
            // 查出数据
            $fileData = UploadFile::select(['id', 'fid', 'name', 'alias'])
                ->whereIn('id', $fileIdArr)
                ->get();


            $fidMap = [
                $fileData->toArray()[0]['fid'] =>
                $distId
            ];

            $deWeightData = self::deWeightNames([
                'distId' => $distId,
                'nameField' => 'alias',
                'model' => new UploadFile,
                'targetData' => $fileData->toArray(),
                'type' => 'file',
            ]);

            $res = self::pasetFilesByData($fileData, $fidMap, $deWeightData);
        
            if ($res !== true) {
                return '复制失败，请重试';
            }
        }

        return true;
    }


    /**
     * 剪切文件夹
     * $params
     *      uid, uid_type 身份辨识
     *      folderIdArr 文件夹id数组
     *      distId  目的地文件夹id
     * 
     * 返回true或错误提示语，执行失败由系统报错
     */
    private static function pasetCutFolder ($params) {

        [
            'uid' => $uid,
            'uid_type' => $uid_type,
            'folderIdArr' => $folderIdArr,
            'distId' => $distId,
        ] = $params;


        // 取出所有后代文件夹
        $sourceData = self::getOffspringFolder($folderIdArr);
        $allData = $sourceData['all'];
        $targetData = $sourceData['target'];

        // 取出所有id
        $allIdList = array_column($allData, 'id');

        // 看后代文件夹id是否与目标文件夹id重叠，有则表示父子关系错误，无法粘贴
        if (in_array($distId, $allIdList)) {
            return '移动失败，目标文件夹是源文件夹的子文件夹';
        }

        // 修正重复的名字，自动递增后缀数字
        $targetDataRes = self::deWeightNames([
            'distId' => $distId,
            'nameField' => 'name',
            'model' => new UploadFolder,
            'targetData' => $targetData,
            'type' => 'folder',
        ]);

        // 更新数据，修改fid和name值
        $ins = new UploadFolder;
        foreach ($targetDataRes as $target) {
            [
                'id' => $id,
                'name' => $name,
            ] = $target;
            
            $ins->where('id', $id)
                ->update([
                    'fid' => $distId,
                    'name' => $name,
                ]);
        }
        
        return true;
    }


    /**
     * 
     */
    private static function pasetCutFile ($params) {

        [
            'uid' => $uid,
            'uid_type' => $uid_type,
            'fileIdArr' => $fileIdArr,
            'distId' => $distId,
        ] = $params;



    }


    /**
     * 剪切文件或文件夹
     */
    public static function pasetCut ($params) {

        $uid = 1;
        $uid_type = 3;
        [
            'idList' => $idList,
            'distId' => $distId,
        ] = $params;

        
        // 把文件和文件夹分成两个数组
        $fileIdArr = [];
        $folderIdArr = [];
        foreach ($idList as $item) {
            $id = $item['id'];
            $type = $item['type'];
            if ($type == 'file') {
                array_push($fileIdArr, $id);
            } else {
                array_push($folderIdArr, $id);
            }
        }

        
        // 判断idList是否为同一个文件夹下的文件，如果不是不允许复制
        $isDataOk = self::isFromSameFid($fileIdArr, $folderIdArr);
        if (!$isDataOk) return '数据不可用，请刷新后重试';

        // 剪切文件夹
        if (count($folderIdArr) > 0) {

            $res = self::pasetCutFolder(compact(
                'uid',
                'uid_type',
                'folderIdArr',
                'distId',
            ));

            if ($res !== true) return $res;

        }

        // 复制文件
        if (count($fileIdArr) > 0) {
            
            // 查出数据
            $fileData = UploadFile::select(['id', 'fid', 'name', 'alias'])
                ->whereIn('id', $fileIdArr)
                ->get();


            // 生成去重化的名字数据
            $deWeightData = self::deWeightNames([
                'distId' => $distId,
                'nameField' => 'alias',
                'model' => new UploadFile,
                'targetData' => $fileData->toArray(),
                'type' => 'file',
            ]);


            // 修改fid和name
            $ins = new UploadFile;
            foreach ($deWeightData as $deWeight) {
                [
                    'id' => $id,
                    'alias' => $alias,
                ] = $deWeight;

                $ins->where('id', $id)->update([
                    'alias' => $alias,
                    'fid' => $distId,
                ]);
            }

        }

        return true;
    }

}
