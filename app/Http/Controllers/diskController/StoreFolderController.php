<?php

namespace App\Http\Controllers\diskController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UploadFolder;
use App\Custom\Common\CustomCommon;

class StoreFolderController extends Controller
{
    

    // 把字符串分成两部分，例如："小明(1)"分成 "小明" 和 "(1)"，"小红(1)(2)" 分成 "小红(1)" 和 "(2)"
    protected static function explodeName ($name) {

        $firstVal = null;
        $lastVal = null;
        
        // 检索有没有(x)的后缀
        preg_match('/(\(\d+\)){1}$/', $name, $p1);

        if (count($p1) > 0) {
            $lastVal = $p1[1];

            // 取出(x)前面的值
            $s = CustomCommon::escapePreg($lastVal);
            preg_match("/^(.*)$s$/", $name, $p2);
            $firstVal = $p2[1];

        } else {
            $firstVal = $name;
        }

        $firstVal = mb_strlen($firstVal) === 0 ? null : $firstVal;
        $lastVal = mb_strlen($lastVal) === 0 ? null : $lastVal;
        return compact('firstVal', 'lastVal');
    }


    // 当 firstVal 和 lastVal 都存在时
    protected static function whenHasTwo ($params) {

        [
            'fid' => $fid,
            'uid' => $uid,
            'uid_type' => $uid_type,
            'firstVal' => $firstVal,
            'lastVal' => $lastVal,
        ] = $params;

        // 检索出形如 "小明"，"小明(1)"，"小明(2)" 的数据
        $escapedFirstVal = CustomCommon::escapeSQL($firstVal);
        $distData = UploadFolder::select('name')
            ->where('uid', $uid)
            ->where('uid_type', $uid_type)
            ->where('fid', $fid)
            ->whereRaw("name REGEXP '^$escapedFirstVal(\\\\([0-9]+\\\\)){0,1}$'")
            ->get()
            ->pluck('name')
            ->toArray();

        // $distData 为空，没有类似的数据，原样返回
        if (count($distData) === 0) return "$firstVal$lastVal";

        // ****不为空，找出当中数字最大的***

        // 取出小括号中间的数字放入数组
        $numArr = [];
        foreach ($distData as $val) {
            // 匹配出有后缀数字的项，数字作为正则的第三个小组返回
            preg_match("/(\((\d+)\)){1}$/", $val, $p1);
            if (count($p1) === 0) continue;
            array_push($numArr, intval($p1[2]));
        }

        // 如果数组numArr长度为0，则表示目的文件夹下类似的文件夹名是不带数字的
        // 而新建的文件夹名是带数字的，不会造成重复，直接返回
        if (count($numArr) === 0) return "$firstVal$lastVal";

        // 如果numArr长度大于0，则降序，取出第一项，与新建文件名的数字进行比较
        // 新建文件名的数字大于numArr的可以使用，否则需要用numArr的 +1 再使用
        preg_match('/^\((\d+)\)$/', $lastVal, $p2);
        $lastValNum = intval($p2[1]);

        // 比较得出较大的数字
        rsort($numArr);
        $numArrNum = array_shift($numArr);
        $bigNum = $lastValNum > $numArrNum ? $lastValNum : ($numArrNum + 1);

        // 最终的文件夹名
        $finalName = "$firstVal($bigNum)";
        
        return $finalName;

    }


    // 当只有firstVal时
    protected static function whenOnlyFirstVal ($params) {

        [
            'fid' => $fid,
            'uid' => $uid,
            'uid_type' => $uid_type,
            'firstVal' => $firstVal,
        ] = $params;

        // 当lastVal不存在时，先判断能不能直接用，能就直接用，不能就假定一个(1)的后缀给它，然后调用whenHasTwo方法
        // 用firstVal查询数据库看有没有这一项，如果没有就可以用，有就不能用
        $isExist = UploadFolder::select('id')
            ->where('uid', $uid)
            ->where('uid_type', $uid_type)
            ->where('fid', $fid)
            ->where('name', $firstVal)
            ->first();

        // 不存在可以直接用
        if ($isExist == null) return $firstVal;

        // 存在，假设一个(1)给lastVal，然后调用whenHasTwo来生成文件夹名
        $lastVal = '(1)';
        $finalName = self::whenHasTwo(compact(
            'uid',
            'uid_type',
            'fid',
            'firstVal',
            'lastVal',
        ));

        return $finalName;
    }


    protected static function whenOnlyLastVal ($params) {
        
        [
            'fid' => $fid,
            'uid' => $uid,
            'uid_type' => $uid_type,
            'lastVal' => $lastVal,
        ] = $params;

        // 当firstVal不存在，表示文件夹名直接是 (x) 形式的
        // 判断能不能直接用，如果不能则给firstVal赋值为lastVal，lastVal赋值为(1)，然后调用whenHasTwo生成新的文件夹名

        // 用lastVal查询数据库看有没有，如果有不能用，没有可以用
        $isExist = UploadFolder::select('id')
            ->where('uid', $uid)
            ->where('uid_type', $uid_type)
            ->where('fid', $fid)
            ->where('name', $lastVal)
            ->first();

        // 不存在，可以用
        if ($isExist == null) return $lastVal;

        // 存在，不可用；把firstVal赋值为lastVal，lastVal赋值为(1)调用whenHasTwo生成新的文件夹名
        $firstVal = $lastVal;
        $lastVal = '(1)';
        $finalName = self::whenHasTwo(compact(
            'uid',
            'uid_type',
            'fid',
            'firstVal',
            'lastVal'
        ));

        return $finalName;
    }
    

    /**
     * 生成一个文件夹名，规定同一父级下文件夹名不能重复，如果重复添加形如 (x) 的后缀
     * @param array $params fid父级id，uid，uid_type确定用户身份，帮助检索，name，输入的文件名
     * @return string $finalName 返回一个可用的文件夹名
     */
    protected static function buildFinalName ($params) {

        [
            'fid' => $fid,
            'uid' => $uid,
            'uid_type' => $uid_type,
            'name' => $name,
        ] = $params;


        [
            'firstVal' => $firstVal,
            'lastVal' => $lastVal
        ] = self::explodeName($name);
        
        $finalName = $name;

        // 当$firstVal存在且$lastVal也存在时
        if ($firstVal != null && $lastVal != null) {
            $finalName = self::whenHasTwo(compact(
                'uid',
                'uid_type',
                'fid',
                'firstVal',
                'lastVal',
            ));
        }

        // 当lastVal不存在时
        if ($firstVal != null && $lastVal == null) {
            $finalName = self::whenOnlyFirstVal(compact(
                'uid',
                'uid_type',
                'fid',
                'firstVal'
            ));
        }

        // 当firstVal不存在时
        if ($firstVal == null && $lastVal != null) {
            $finalName = self::whenOnlyLastVal(compact(
                'uid',
                'uid_type',
                'fid',
                'lastVal'
            ));
        }

        return $finalName;
    }


    public static function save ($params) {
        
        $uid = 1;
        $uid_type = 3;
        [
            'fid' => $fid,
            'folderName' => $name
        ] = $params;

        // 生成文件夹名
        $finalName = self::buildFinalName(compact(
            'fid',
            'uid',
            'uid_type',
            'name',
        ));

        // 插入数据库
        $res = UploadFolder::create([
            'fid' => $fid,
            'uid' => $uid,
            'uid_type' => $uid_type,
            'name' => $finalName,
        ]);

        $insertId = $res->id;
        return $res->id;
    }

}
