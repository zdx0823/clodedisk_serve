<?php
namespace App\Clodedisk\Common;

use Illuminate\Support\Facades\Validator;

class ClodediskCommon {

  /**
   * 返回失败的结果，$realMsg提示语，默认为空；$msgArr提示语数组，$data数据。
   * 返回一个数组，status = -1, fakeMsg是同一的假提示语
   */
  public static function makeErrRes ($realMsg = '', $msgArr = [], $data = []) {
    return [
        'status' => -1,
        'msg' => '参数错误，请重试',
        'fakeMsg' => '服务错误，请重试',
        'realMsg' => $realMsg,
        'msgArr' => $msgArr,
        'data' => $data
    ];
  }


  /**
   * 返回成功的结果，$data 数据，$msg 提示语，默认为'操作成功'。返回一个数组
   * @param array 数据
   * @param string 提示语，默认为'操作成功'
   * 
   * @return array
   */
  public static function makeSuccRes ($data = [], $msg = '操作成功') {
    $status = 1;
    return compact('status', 'msg', 'data');
}

  
  /**
   * 声明一个自定义验证规则
   * 两个属性只存在其中之一时返回true
   * 使用方法：
   * Validator::make($data, [
   *   'fid' => '$without:path'
   * ])
   * 当path不存在时，fid判断为true
   */
  public static function validateWithOut () {
    
    Validator::extendImplicit('$without', function ($attribute, $value, $parameters, $validator) {

      $validator->message = 123;
      // 被排除的属性是否存在，不存在返回true
      if (!isset($parameters[0])) {
          return true;
      }

      $data = $validator->attributes();  // 待验证的属性数组

      // 当前属性存在且被排除属性不存在
      if (array_key_exists($attribute, $data) && !array_key_exists($parameters[0], $data)) {
          return true;
      }

      // 当前属性不存在，但被排除属性存在
      if (!array_key_exists($attribute, $data) && array_key_exists($parameters[0], $data)) {
          return true;
      }

      $needKey = $attribute;
      $withoutKey = $parameters[0];

      return false;
    });

    Validator::replacer('$without', function ($message, $attribute, $rule, $parameters) {

      return str_replace(':withoutKey', $parameters[0], $message);
      
    });

  }


  // 转义正则表达式的特殊字符
  public static function escapePreg ($str) {

    $str = str_replace('(', '\(', $str);
    $str = str_replace(')', '\)', $str);
    $str = str_replace('.', '\.', $str);

    return $str;
  }

  // 转义数据库正则表达式的特殊字符
  public static function escapeSQL ($str) {

    $str = str_replace('(', '\\\\(', $str);
    $str = str_replace(')', '\\\\)', $str);
    $str = str_replace('.', '\\\\.', $str);

    return $str;
  }


  // 根据系统分隔符合并路径，接收一个数组，返回一个路径
  public static function mergePath ($arr) {
    return implode(DIRECTORY_SEPARATOR, $arr);
  }


    /**
     * 把字符串分成两部分，例如："小明(1)"分成 "小明" 和 "(1)"，"小红(1)(2)" 分成 "小红(1)" 和 "(2)"
     * $oriName 文件名；$isHasExt是否有后缀，布尔值，如果给为true，则将最后的 .xx 视作后缀
     * 
     * 返回一个数组，
     *    firstVal小括号前面部分，
     *    lastVal最后一个小括号，
     *    ext后缀，ext默认为空字符串，ext是带.的后缀
     */
    public static function explodeName ($oriName, $isHasExt = false) {

      $firstVal = null;
      $lastVal = null;

      $ext = '';
      $name = $oriName;

      if ($isHasExt) {
        preg_match('/(\.[^\.]+){1}$/', $oriName, $p1);
        if (count($p1) > 0) {
          $ext = $p1[1];

          $nameLen = mb_strlen($name);
          $extLen = mb_strlen($ext);

          $name = substr($name, 0, $nameLen - $extLen);
        }
      }
      
      // 检索有没有(x)的后缀
      preg_match('/(\(\d+\)){1}$/', $name, $p1);

      if (count($p1) > 0) {
            $lastVal = $p1[1];

            // 取出(x)前面的值
            $s = ClodediskCommon::escapePreg($lastVal);
            preg_match("/^(.*)$s$/", $name, $p2);
            $firstVal = $p2[1];

        } else {
            $firstVal = $name;
        }

        $firstVal = mb_strlen($firstVal) === 0 ? null : $firstVal;
        $lastVal = mb_strlen($lastVal) === 0 ? null : $lastVal;

        return compact('firstVal', 'lastVal', 'ext');
    }


    /**
     * 根据文件名取得后缀
     * 返回不带点的后缀名
     */
    public static function getExtByName ($name) {

        $exploded = explode('.', $name);
        if (count($exploded) === 1) {
          return '';
        }

        return array_pop($exploded);
    }

}