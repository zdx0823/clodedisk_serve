<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CheckParams
{

    private $message = [
        'required' => '路由错误，缺少参数[:attribute]',
        'numeric' => '路由错误，参数[:attribute]必须为数字类型',
        'min' => '路由错误，参数[:attribute]不能小于 :min',
        'in' => '路由错误，参数[:attribute]类型必须是 :values'
    ];

    /**
     * 构建错误返回信息
     * $res Validator::make返回的实例
     * $result  返回值，数组。msg为简单的信息，realMsg真实信息，msgArr关联数组
     */
    private function makeErrRes ($res) {
        $str = '';
        $arr = [];
        $msgArr = $res->errors()->toArray();
        foreach ($msgArr as $key => $val) {
            $arr[$key] = $val[0];
            $str .= ($val[0] . '\n');
        }
        $str = rtrim($str, '\n');

        $result = [
            'status' => -1,
            'msg' => '参数错误，请重试',
            'fakeMsg' => '服务错误，请重试',
            'realMsg' => $str,
            'msgArr' => $msgArr,
            'data' => []
        ];

        return $result;
    }


    private function show ($request) {
        // 路由参数
        $res = Validator::make($request->route()->parameters, [
            'fid' => 'bail|required|numeric'
        ], $this->message);

        if ($res->fails() !== false) return $this->makeErrRes($res);

        // query字段
        $queryRes = Validator::make($request->query(), [
            'page' => 'bail|numeric|min:1',
            'pagesize' => 'bail|numeric|min:10',
            'order' => [
                Rule::in(['asc', 'desc', 'ASC', 'DESC'])
            ]
        ], $this->message);

        if ($queryRes->fails() !== false) return $this->makeErrRes($queryRes);

        $request->page = isset($request->page) ? $request->page : 1;
        $request->pagesize = isset($request->pagesize) ? $request->pagesize : 10;
        $request->order = isset($request->order) ? $request->order : 'desc';

        return true;
    }


    /**
     * 检索出路由名，路由名即此类的方法名，如果返回非true值就是参数错误
     * 路由名对应的方法，接收一个$request，返回true或一个数组
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $routeName = $request->route()->getName();
        $res = $this->$routeName($request);
        if ($res !== true) {
            return response()->json($res);
        }
        
        return $next($request);
    }
}
