<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Clodedisk\Common\ClodediskCommon;

class CheckParams
{


    /**
     * 构建错误返回信息
     * $res Validator::make返回的实例
     * $result  返回值，数组。msg为简单的信息，realMsg真实信息，msgArr关联数组
     */
    public function makeErrRes ($res) {
        $str = '';
        $arr = [];
        $msgArr = $res->errors()->toArray();
        foreach ($msgArr as $key => $val) {
            $arr[$key] = $val[0];
            $str .= ($val[0] . '\n');
        }
        $str = rtrim($str, '\n');

        $result = ClodediskCommon::makeErrRes($str, $msgArr);
        return $result;
    }


    private function list ($request) {

        // query字段
        $validateData = $request->query();
        $queryRes = Validator::make($validateData, [
            'fid' => 'bail|$without:path|numeric',
            'path' => 'bail|$without:fid|string',
            'page' => 'bail|numeric|min:1',
            'pagesize' => 'bail|numeric|min:10',
            'order' => [
                Rule::in(['asc', 'desc', 'ASC', 'DESC'])
            ]
        ]);

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
