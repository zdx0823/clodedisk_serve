<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;

use App\Custom\Common\CustomCommon;

class CheckAuth
{

    private $needJsonRoute = [];
    private $needViewRoute = ['indexPage'];

    /**
     * 是否登录超时
     * 根据 config('custom.user_session_key')的session中的 id 和 timeout判断
     * 当前时间大于timeout表示超时
     * 
     * 返回布尔值
     */
    private function isAuthTimeout () {

        [
            'id' => $id,
            'timeout' => $timeout
        ] = session()->get(config('custom.user_session_key'));

        // var_dump(session()->all());
        if ($id == null) return false;
        if (time() > $timeout) return false;

        return true;
    }


    private function attemptLogin ($st) {

        $client = new Client;

        $clientRes = $client->request('POST', 'http://localhost:90/check_st', [
            'form_params' => ['st' => $st]
        ]);

        // 返回结果是字符串，期望值 ['data' => ['user' => xxxx]]
        // 'user' 是加密值，期望解密后为 ['id', 'timeout'] 的json字符串
        $data = null;
        try {

            $data = json_decode($clientRes->getBody(), true);
            if (!is_array($data)) {
                $data = null;
                return;
            }

            $data = $data['data'];
            if (!array_key_exists('user', $data)) {
                $data = null;
                return;
            }

            $user = CustomCommon::decrypt($data['user']);
            if (!$user) {
                $data = null;
                return;
            }

            $data = json_decode($user, true);
            if (!array_key_exists('id', $data) || !array_key_exists('timeout', $data)) $data = null;

        } catch (ServerException $e) {}

        // $data 为null，返回值错误，未登录
        if ($data == null) return false;

        // 记录成登录状态
        $sessionKey = config('custom.user_session_key');

        session([ $sessionKey => $data ]);

        return true;
    }


    public function handle(Request $request, Closure $next)
    {

        // 判断需要返回视图还是json数据
        $routeName = $request->route()->getName();

        $SSO_URL = config('custom.sso_url');
        $curUrl = $request->url();
        $SSO_URL = "$SSO_URL?serve=$curUrl";

        // json返回值
        $jsonRes = CustomCommon::makeErrRes(
            '未登录，请登录后操作',
            [],
            [ 'sso' => $SSO_URL ],
            -2,
        );

        // view返回值
        $viewRes = redirect($SSO_URL);
        $res = \in_array($routeName, $this->needViewRoute)
            ? $viewRes
            : response()->json($jsonRes);

        $st = $request->st;

        // var_dump($this->isAuthTimeout());
        // 没登录
        if (!$this->isAuthTimeout()) {

            // 且没有st
            if ($st == null) return $res;

            // 有st，根据st，尝试登录
            $attemptRes = self::attemptLogin($st);

            // 尝试失败，返回
            if (!$attemptRes) return $res;

            // var_dump(session()->all());
            // 通过，执行next
            return $next($request);
        }

        // 已登录，且未超时，正常登录
        return $next($request);
    }
}
