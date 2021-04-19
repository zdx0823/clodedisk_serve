<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Custom\Common\CustomCommon;

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


    // list接口
    private function list ($request) {

        // query字段
        $validateData = $request->input();
        $res = Validator::make($validateData, [
            'fid' => 'bail|$without:path|numeric',
            'path' => 'bail|$without:fid|string',
            'page' => 'bail|numeric|min:1',
            'pagesize' => 'bail|numeric|min:10',
            'order' => [
                Rule::in(['asc', 'desc', 'ASC', 'DESC'])
            ]
        ]);

        if ($res->fails() !== false) return $this->makeErrRes($res);

        return true;
    }


    // storeFolder接口
    private function storeFolder ($request) {

        $validateData = $request->input();
        $res = Validator::make($validateData, [
            'fid' => 'bail|required|numeric',
            'folderName' => ['bail', 'required', 'regex:/^[^\\\\\/\:\*\?\"\<\>\|]{1,}$/', 'min:1', 'max:16']
        ], [
            'regex' => '文件名不能包含下列任何字符：\\/:*?"<>|'
        ]);

        if ($res->fails() !== false) return $this->makeErrRes($res);

        return true;
    }


    // updateFileName接口
    private function updateFileName ($request) {
        $validateData = $request->input();
        $res = Validator::make($validateData, [
            'id' => 'bail|required|numeric|exists:upload_file,id',
            'fid' => 'bail|required|numeric',
            'name' => ['bail', 'required', 'regex:/^[^\\\\\/\:\*\?\"\<\>\|]{1,}$/', 'min:1', 'max:16'],
        ], [
            'regex' => '文件名不能包含下列任何字符：\\/:*?"<>|'
        ]);

        if ($res->fails() !== false) return $this->makeErrRes($res);

        return true;
    }


    // updateFolderName
    private function updateFolderName ($request) {
        $validateData = $request->input();
        $res = Validator::make($validateData, [
            'id' => 'bail|required|numeric|exists:upload_folder,id',
            'fid' => 'bail|required|numeric',
            'name' => ['bail', 'required', 'regex:/^[^\\\\\/\:\*\?\"\<\>\|]{1,}$/', 'min:1', 'max:16'],
        ], [
            'regex' => '文件名不能包含下列任何字符：\\/:*?"<>|'
        ]);

        if ($res->fails() !== false) return $this->makeErrRes($res);

        return true;
    }


    // 上传文件参数判断
    private function upload ($request) {
        $validateData = $request->input();
        $res = Validator::make($validateData, [
            'fid' => 'bail|required|numeric',
            'qqpartindex' => 'bail|required|numeric',
            'qqpartbyteoffset' => 'bail|required|numeric',
            'qqchunksize' => 'bail|required|numeric',
            'qqtotalparts' => 'bail|required|numeric',
            'qqtotalfilesize' => 'bail|required|numeric',
            'qqfilename' => 'bail|required|string',
            'qquuid' => 'bail|required|string',
        ]);

        if ($res->fails() !== false) return $this->makeErrRes($res);

        return true;
    }


    // 复制文件或文件夹
    private function copyResource ($request) {
        $validateData = $request->input();
        $res = Validator::make($validateData, [
            'idList' => 'bail|required|array',
            'idList.*' => 'bail|required|array',
            'idList.*.id' => 'bail|required|numeric',
            'idList.*.type' => 'bail|required|in:file,folder',
            'distId' => 'bail|required|numeric',
        ]);

        if ($res->fails() !== false) return $this->makeErrRes($res);

        return true;
    }


    private function cutResource ($request) {
        $validateData = $request->input();
        $res = Validator::make($validateData, [
            'idList' => 'bail|required|array',
            'idList.*' => 'bail|required|array',
            'idList.*.id' => 'bail|required|numeric',
            'idList.*.type' => 'bail|required|in:file,folder',
            'distId' => 'bail|required|numeric',
        ]);

        if ($res->fails() !== false) return $this->makeErrRes($res);

        return true;
    }


    private function destroy ($request) {
        $validateData = $request->input();
        $res = Validator::make($validateData, [
            'idList' => 'bail|required|array',
            'idList.*' => 'bail|required|array',
            'idList.*.id' => 'bail|required|numeric',
            'idList.*.type' => 'bail|required|in:file,folder',
        ]);

        if ($res->fails() !== false) return $this->makeErrRes($res);

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
