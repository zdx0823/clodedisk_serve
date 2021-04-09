<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UploadFolder;
use App\Models\UploadFile;
use App\Clodedisk\Common\ClodediskCommon;

class DiskController extends Controller
{

    public function __construct () {
        $this->middleware(function ($request, $next) {
            return $this->middlewareGetBaseId($request, $next);
        });
    }


    // 获取用户的顶层目录id
    // 如果fid==0表示要获取用户顶层目录下的文件，取出该用户的顶层目录id
    protected function middlewareGetBaseId ($request, $next) {

        $routeName = $request->route()->getName();
        $list = ['list', 'storeFolder'];

        // 路由不需要fid或fid不为0跳过
        if (!in_array($routeName, $list)) return $next($request);
        if ($request->fid != 0) return $next($request);

        $uid = 1;
        $uid_type = 3;
        $fid = UploadFolder::select(['id'])
                ->where('uid_type', $uid_type)
                ->where('uid', $uid)
                ->where('fid', null)
                ->first()->id;

        $request->json()->set('fid', $fid);
        return $next($request);
    }


    /**
     * 格式化返回结果
     * @param array $data 返回数据
     * @param string $msg 消息提示
     * @return array status = 1, msg, data
     */
    protected static function doRes ($data = [], $msg = '操作成功') {
        $status = 1;
        return compact('status', 'msg', 'data');
    }

    /**
     * 生成面包屑
     * @param fid
     * @return array 二维数组，第一项是最外层文件夹
     * 
     * @return array path: 完整的路径; crumb 数组，第一项是靠外层的文件夹
     */
    protected function buildCrumb ($fid) {

        $curFid = $fid;
        $data = [];

        // 向上遍历直到用户的顶层文件夹
        do {

            $crumbOne = UploadFolder::select(['id', 'fid', 'name'])->where('id', $curFid)->first();
            $curFid = $crumbOne->fid;
            array_unshift($data, $crumbOne->toArray());

        } while ($curFid !== NULL);

        // 修改第一项的值
        $data[0]['fid'] = 0;
        $data[0]['name'] = '全部文件';
    
        // 合并成字符串，提出“全部文件”，最终路径形如： /文件夹/子文件夹/孙文件夹，/表示顶层目录
        $path = implode('/', array_column($data, 'name'));
        $path = str_replace('全部文件', '', $path);

        // 从后向前取出3条做面包屑
        $crumb = [];
        foreach ($data as $item) {
            if (count($crumb) === 3) {
                break;
            }
            array_unshift($crumb, array_pop($data));
        }

        return compact('path', 'crumb');
    }


    /**
     * 生成图片路径，根据文件后缀，给文件项添加图片路径，前缀为env('RESOURCE_IMG_URL')
     * @param array
     * @return array  img_path 大图，img_path_sm 小图
     */
    protected function buildImgPath ($item) {
        if ($item['type'] === 'folder') return $item;

        $ext = $item['extend_info']['ext'];
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) return $item;

        $item['img_path'] = env('RESOURCE_IMG_URL') . '/' . $item['name'];
        $item['img_path_sm'] = $item['img_path'] . '?w=300';

        return $item;
    }


    /**
     * 根据fid查询该文件夹下的文件和文件夹
     * @param array uid, uid_type 用户确定用户和它的身份，limit,offset分页，fid要获取的文件夹id，order排序方法
     * @return array data数据，tPath当前路径，crumb数组，面包屑
     */
    protected function listByFid ($params) {

        [
            'uid' => $uid,
            'uid_type' => $uid_type,
            'fid' => $fid,
            'offset' => $offset,
            'limit' => $limit,
            'order' => $order
        ] = $params;
        

        // 检查id = fid 的文件夹存不存在
        $isFidExist = UploadFolder::select(['id'])
            ->where('id', $fid)
            ->where('uid', $uid)
            ->where('uid_type', $uid_type)
            ->first();
        if ($isFidExist == null) {
            return false;
        }


        // 拿出10个文件夹
        $folders = UploadFolder::select(['id', 'fid', 'name', 'ctime'])
            ->where('uid_type', $uid_type)
            ->where('uid', $uid)
            ->where('fid', $fid)
            ->orderBy('ctime', $order)
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();
            
        // 拿出10个文件
        $files = UploadFile::select(['id', 'name', 'alias', 'fid', 'ctime'])
            ->with('extend_info')
            ->where('fid', $fid)
            ->orderBy('ctime', $order)
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->toArray();

        // 合并拼成一个数组，取出对应数量的数据，先文件夹，后文件
        $data = [];
        for ($i = 0; $i < $limit; $i++) {
            if (count($folders) > 0) {
                $data[$i] = array_shift($folders);
            } else if (count($files) > 0) {
                $data[$i] = array_shift($files);
            } else {
                break;
            }
        }

        // 生成面包屑
        $crumbData = $this->buildCrumb($fid);
        $tPath = $crumbData['path'];
        $crumb = $crumbData['crumb'];

        // 给每一项添加path
        $data = array_map(function ($item) use ($tPath) {
            $item['path'] = $item['type'] === 'folder'
                ? $tPath . '/' . $item['name']
                : $tPath . '/' . $item['alias'];

            $item = $this->buildImgPath($item);
            return $item;
        }, $data);

        // 如果$path为空字符串，表示当前再往上就是顶层目录，需给它赋值为 /
        $tPath = $tPath === '' ? '/' : $tPath;

        return compact('data', 'crumb', 'tPath', 'fid');
    }


    /**
     * 查出路径对应的fid
     * @param $baseId 用户顶层目录的id，$path 文件夹路径
     * @return number 正确返回fid值，错误返回-1
     */
    protected function retrieveFidByPath ($baseId, $path) {

        // 分解成数组，剔除空字符串项，把 \文件夹\子文件夹\孙文件夹 转成 ['文件夹', '子文件夹', '孙文件夹']
        $arr = [];
        $pathArr = explode('/', $path);
        foreach ($pathArr as $item) {
            if (mb_strlen($item) > 0) {
                array_push($arr, $item);
            }
        }

        // 第一次查询的fid即顶层目录的id
        $preFid = $baseId;

        // 路径是否正确
        $isPathOk = true;

        // 根据$arr依次比对数据库的记录
        // 每次查询的id作为下一次查询的fid
        for ($i = 0, $len = count($arr); $i < $len; $i++) {
            $_name = $arr[$i];
            $res = UploadFolder::select(['id'])
                ->where('fid', $preFid)
                ->where('name', $_name)
                ->first();
      
            // 如果没有结果则表示路径错误，不用再继续
            if ($res == null) {
              $isPathOk = false;
              break;
            }
      
            // 取出id作为下一次的fid
            $preFid = $res->toArray()['id'];
        }

        // 路径正确返回最后一次查到的id，路径错误返回-1
        return $isPathOk ? $preFid : -1;
    }


    /**
     * 根据路径查出对应文件夹下的文件和文件夹
     * 先查出路径对应的fid，再调用listByFid查询
     * 
     * @param array uid, uid_type 用户确定用户和它的身份，limit,offset分页，fid要获取的文件夹id，order排序方法
     * @return array data数据，tPath当前路径，crumb数组，面包屑
     */
    protected function listByPath ($params) {
        
        [
            'uid' => $uid,
            'uid_type' => $uid_type,
            'path' => $path,
            'offset' => $offset,
            'limit' => $limit,
            'order' => $order
        ] = $params;

        // 获取用户顶层目录id
        $baseId = UploadFolder::select(['id'])
            ->where('fid', null)
            ->where('uid', $uid)
            ->where('uid_type', $uid_type)
            ->first()
            ->value('id');

        $fid = $this->retrieveFidByPath($baseId, $path);

        // fid = -1 表示路径不对
        if ($fid === -1) {
            return false;
        }

        unset($params['path']);
        $params['fid'] = $fid;
        
        return $this->listByFid($params);
    }


    // 文件，文件夹列表
    public function list (Request $request) {

        $uid = 1;
        $uid_type = 3;
        [
            'fid' => $fid,
            'path' => $path,
            'page' => $page,
            'pagesize' => $pagesize,
            'order' => $order
        ] = $request->json()->all();
        $offset = ($page - 1) * $pagesize;
        $limit = $pagesize;

        // 合并成数组
        $params = compact('uid', 'uid_type', 'offset', 'limit', 'order');

        // 添加fid值或path值
        // 如果两者都存在 或者 只存在path 使用listByPath
        if (($path != null && $fid != null) || ($path != null && $fid == null)) {
            $params['path'] = $path;
            $resData = $this->listByPath($params);
        } else {
            // 否则使用listByFid
            $params['fid'] = $fid;
            $resData = $this->listByFid($params);
        }


        // 返回false表示文件夹不存在
        if ($resData === false) {
            return ClodediskCommon::makeErrRes('路由错误，所选文件夹不存在或已被删除');
        }


        // 结构结果
        [
            'data' => $data,
            'crumb' => $crumb,
            'tPath' => $tPath,
            'fid' => $tFid
        ] = $resData;

        // 返回结果
        return self::doRes([
            'data' => $data,
            'crumbData' => $crumb,
            'fid' => $tFid,
            'path' => $tPath
        ], '获取成功');
    }


    /**
     * 新建文件夹
     * @param Object fid父级文件夹id，folderName文件夹名
     */
    public function storeFolder (Request $request) {
        
        $uid = 1;
        $uid_type = 3;
        [
            'fid' => $fid,
            'folderName' => $folderName
        ] = $request->json()->all();
        
        
        // 判断父级文件夹是否存在
        $isFidExist = UploadFolder::select('id')
            ->where('uid', $uid)
            ->where('uid_type', $uid_type)
            ->where('id', $fid)
            ->first();
        // var_dump($isFidExist);
        if ($isFidExist == null) {
            return ClodediskCommon::makeErrRes('所在文件夹不存在或已被删除，请重试');
        }

        // 插入
        $insertId = diskController\StoreFolderController::save(compact(
            'fid',
            'folderName',
        ));

        return self::doRes(compact('insertId'), '新建成功');
    }

    // 上传文件
    public function upload (Request $request) {
        return 'upload';
    }


    /**
     * 修改文件名
     * @param Object 期望接收 id修改的文件，fid所在父级文件夹，name新名称，name是带后缀的
     */
    public function updateFileName (Request $request) {

        $uid = 1;
        $uid_type = 3;
        [
            'id' => $id,
            'fid' => $fid,
            'name' => $name,
        ] = $request->json()->all();

        // 文件名是否存在，同一父级下alias不允许重复
        $isExist = UploadFile::select('id')
            ->where('fid', $fid)
            ->where('alias', $name)
            ->first();

        // 重复
        if ($isExist && $isExist->id != $id) return ClodediskCommon::makeErrRes('文件名重复，请重新输入'); 

        // 名称可用
        
        // 实例化一个模型，限制两个条件，fid和id都相同时
        $file = UploadFile::where('fid', $fid)->where('id', $id)->first();

        // 文件不存在
        if ($file == null) return ClodediskCommon::makeErrRes('修改失败，文件不存在或已被删除'); 
        
        // 取出原文件后缀名，拼成alias值
        $explodedName = explode('.', $name);
        $name = array_shift($explodedName);
        $ext = $file->extend_info->ext;
        $name = "$name.$ext";

        // 更改
        $file->alias = $name;
        $file->save();

        // 更改成功
        return self::doRes([], '重命名成功');
    }
    

    /**
     * 修改文件夹名
     * @param Object 期望接收 id修改的文件，fid所在父级文件夹，name新名称
     */
    public function updateFolderName (Request $request) {
        
        $uid = 1;
        $uid_type = 3;
        [
            'id' => $id,
            'fid' => $fid,
            'name' => $name,
        ] = $request->json()->all();

        // 文件名是否存在，同一父级下alias不允许重复
        $isExist = UploadFolder::select('id')
            ->where('fid', $fid)
            ->where('name', $name)
            ->first();

        // 重复
        if ($isExist && $isExist->id != $id) return ClodediskCommon::makeErrRes('文件名重复，请重新输入'); 

        // 名称可用
        
        // 实例化一个模型，限制两个条件，fid和id都相同时
        $folder = UploadFolder::where('fid', $fid)->where('id', $id)->first();

        // 文件夹不存在
        if ($folder == null) return ClodediskCommon::makeErrRes('修改失败，文件夹不存在或已被删除'); 

        // 更改
        $folder->name = $name;
        $folder->save();

        // 更改成功
        return self::doRes([], '重命名成功');
    }
    
    // 复制，剪切文件或文件夹
    public function changeResource (Request $request) {
        return 'changeResource';
    }
    
    // 删除文件或文件夹
    public function destroy (Request $request) {
        return 'destroy';
    }
}
