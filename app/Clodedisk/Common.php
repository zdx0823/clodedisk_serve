<?php
namespace App\Clodedisk\Common;

use Illuminate\Support\Facades\Validator;

class ClodediskCommon {

  /**
   * 返回失败的结果，$realMsg提示语，默认为空；$msgArr提示语数组，$data数据。
   * 返回一个数组，status = 1, fakeMsg是同一的假提示语
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

    return $str;
  }

  // 转义数据库正则表达式的特殊字符
  public static function escapeSQL ($str) {

    $str = str_replace('(', '\\\\(', $str);
    $str = str_replace(')', '\\\\)', $str);

    return $str;
  }


  // 根据系统分隔符合并路径，接收一个数组，返回一个路径
  public static function mergePath ($arr) {
    return implode(DIRECTORY_SEPARATOR, $arr);
  }

}