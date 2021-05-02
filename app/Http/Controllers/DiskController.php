<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UploadFolder;
use App\Models\UploadFile;
use App\Custom\Common\CustomCommon;
use App\Custom\UserInfo\UserInfo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $uid = UserInfo::id();

        // 如果uid为1表示为管理员，管理员的fid为null，其他的fid为2
        $preFid = $uid === 1
            ? null
            : 2;

        $fid = UploadFolder::select(['id'])
                ->where('uid', $uid)
                ->where('fid', '=', $preFid)
                ->first()->id;

        $request->fid = $fid;
        return $next($request);
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

        $pathList = [];
        // 向上遍历直到用户的顶层文件夹
        do {

            $crumbOne = UploadFolder::select(['id', 'fid', 'name'])
                ->where('id', $curFid)
                ->first()
                ->toArray();

            $curFid = $crumbOne['fid'];
            array_unshift($data, $crumbOne);

        } while ($curFid !== NULL);

        // 修改第一项的值
        $data[0]['fid'] = 0;
        $data[0]['name'] = '全部文件';

        $nameList = array_column($data, 'name');
        $nameList[0] = '';

        // 给面包屑添加path参数
        for ($i = 0, $len = count($nameList); $i < $len; $i++) {
            $curPath = $i === 0
                ? '/'
                : implode('/', array_slice($nameList, 0, $i + 1));
            $data[$i]['path'] = $curPath;
        }
    
        $path = implode('/', $nameList);

        // 从后向前取出3条做面包屑
        $crumb = [];
        foreach ($data as $item) {
            if (count($crumb) === 3) break;
            array_unshift($crumb, array_pop($data));
        }

        return compact('path', 'crumb');
    }


    /**
     * 生成图片路径，根据文件后缀，给文件项添加图片路径，前缀为config('custom.resource_img_url')
     * @param array
     * @return array  img_path 大图，img_path_sm 小图
     */
    protected function buildImgPath ($item) {
        if ($item['type'] === 'folder') return $item;

        $ext = $item['extend_info']['ext'];
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) return $item;

        // $item['img_path'] = config('custom.resource_img_url') . '/' . $item['name'];
        $item['img_path'] = "http://localhost:89/api/clodedisk/img/" . $item['name'];
        $item['img_path_sm'] = $item['img_path'] . '?w=300';

        return $item;
    }


    /**
     * 根据fid查询该文件夹下的文件和文件夹
     * $params uid, uid_type 用户确定用户和它的身份，limit,offset分页，fid要获取的文件夹id，order排序方法
     * 
     * 返回 data数据，tPath当前路径，crumb数组，面包屑
     */
    protected function listByFid ($params) {

        [
            'fid' => $fid,
            'offset' => $offset,
            'limit' => $limit,
            'order' => $order
        ] = $params;
        

        // 检查id = fid 的文件夹存不存在
        $isFidExist = UploadFolder::select(['id'])
            ->where('id', $fid)
            ->first();
        if ($isFidExist == null) {
            return false;
        }


        // 拿出10个文件夹
        $folders = UploadFolder::select(['id', 'fid', 'name', 'ctime'])
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
     * $params uid, uid_type 用户确定用户和它的身份，limit,offset分页，fid要获取的文件夹id，order排序方法
     * 
     * 返回 data数据，tPath当前路径，crumb数组，面包屑
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

        $fid = $request->input('fid', null);
        $path = $request->input('path', null);
        $page = $request->input('page', 1);
        $pagesize = $request->input('pagesize', 10);
        $order = $request->input('order', 'desc');

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
            return CustomCommon::makeErrRes('路由错误，所选文件夹不存在或已被删除');
        }


        // 结构结果
        [
            'data' => $data,
            'crumb' => $crumb,
            'tPath' => $tPath,
            'fid' => $tFid
        ] = $resData;

        // 返回结果
        return CustomCommon::makeSuccRes([
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
        ] = $request->input();
        
        // 判断父级文件夹是否存在
        $isFidExist = UploadFolder::select('id')
            ->where('uid', $uid)
            ->where('uid_type', $uid_type)
            ->where('id', $fid)
            ->first();

        if ($isFidExist == null) {
            return CustomCommon::makeErrRes('所在文件夹不存在或已被删除，请重试');
        }

        // 插入
        $insertId = diskController\StoreFolderController::save(compact(
            'fid',
            'folderName',
        ));

        return CustomCommon::makeSuccRes(compact('insertId'), '新建成功');
    }


    /**
     * 合并文件
     * params需要storageTmp，tmpDir，qqfilename，qquuid
     * 返回最终的文件名
     */
    private function uploadMerge ($params) {
        
        [
            'storageTmp' => $storageTmp,
            'tmpDir' => $tmpDir,
            'qqfilename' => $qqfilename,
            'qquuid' => $qquuid
        ] = $params;

        // 取出分片列表
        $pathList = $storageTmp->files($tmpDir);
        sort($pathList);

        // 循环追加成一个文件
        $filePath = $tmpDir . DIRECTORY_SEPARATOR . $qqfilename;
        foreach ($pathList as $chunkPath) {
            $storageTmp->append(
                $filePath,
                $storageTmp->get($chunkPath),
                null
            );
        }

        // 删除该分片
        $storageTmp->delete($chunkPath);

        // 生成个随机文件名，移动到 upload/files 目录
        $explodeQQfilename = explode('.', $qqfilename);
        $ext = count($explodeQQfilename) > 1 ? array_pop($explodeQQfilename) : '';
        $fileName = md5($qquuid . $qqfilename . Carbon::now()) . '.' . $ext;
        
        Storage::move(
            CustomCommon::mergePath(['upload', 'tmp', $tmpDir, $qqfilename]),
            CustomCommon::mergePath(['upload', 'files', $fileName]),
        );

        // 删除临时文件夹
        $storageTmp->deleteDirectory($tmpDir);

        return $fileName;
    }


    /**
     * 写入数据库
     * params需要fid，finalName
     * 返回插入的id
     */
    private function insertToDB ($params) {

        [
            'fid' => $fid,
            'finalName' => $name
        ] = $params;

        $alias = substr($name, mb_strlen($name) - 16);

        $file = UploadFile::create(compact('fid', 'name', 'alias'));
        return $file->id;
    }


    // 上传文件
    public function upload (Request $request) {

        [
            'qqpartindex' => $qqpartindex,
            'qqpartindex' => $qqpartindex,
            'qqpartbyteoffset' => $qqpartbyteoffset,
            'qqchunksize' => $qqchunksize,
            'qqtotalparts' => $qqtotalparts,
            'qqtotalfilesize' => $qqtotalfilesize,
            'qqfilename' => $qqfilename,
            'qquuid' => $qquuid,
            'fid' => $fid,
        ] = $request->input();

        // 超过50M禁止上传
        if ($qqtotalfilesize > config('custom.upload_max_size')) {
            return CustomCommon::makeErrRes('超过50M的文件禁止上传');
        }

        // 获取文件生成磁盘实例
        $qqfile = $request->file('qqfile');
        $storageTmp = Storage::disk('uploadTmp');

        // 创建临时文件夹
        $tmpDir = $qquuid;
        $dirList = $storageTmp->directories(DIRECTORY_SEPARATOR);
        if (!in_array($tmpDir, $dirList)) {
            $storageTmp->makeDirectory($tmpDir);
        }
        
        // 把分片移入它的临时文件夹
        $storageTmp->putFileAs(
            $tmpDir,
            $qqfile,
            $qqpartindex
        );

        // 如果是最后一个分片执行合并动作
        if ($qqpartindex == $qqtotalparts - 1) {
            $finalName = $this->uploadMerge(compact(
                'storageTmp',
                'tmpDir',
                'qqfilename',
                'qquuid'
            ));

            // 生成alias
            $alias = substr($finalName, mb_strlen($finalName) - 16);

            // 插入数据库记录
            $insertId = diskController\commonController::insertFileToDB([
                'fid' => $fid,
                'name' => $finalName,
                'alias' => $alias,
                'ext' => CustomCommon::getExtByName($qqfilename),
                'size' => $qqtotalfilesize,
            ], true);


            if ($insertId == null) {

                return CustomCommon::makeErrRes('上传失败，请重试');

            } else {

                return CustomCommon::makeSuccRes([
                    'success' => true,
                    'insertId' => $insertId
                ], '上传成功');

            }
        }

        return [
            'success' => true
        ];
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
        ] = $request->input();

        // 文件名是否存在，同一父级下alias不允许重复
        $isExist = UploadFile::select('id')
            ->where('fid', $fid)
            ->where('alias', $name)
            ->first();

        // 重复
        if ($isExist && $isExist->id != $id) return CustomCommon::makeErrRes('文件名重复，请重新输入'); 

        // 名称可用
        
        // 实例化一个模型，限制两个条件，fid和id都相同时
        $file = UploadFile::where('fid', $fid)->where('id', $id)->first();

        // 文件不存在
        if ($file == null) return CustomCommon::makeErrRes('修改失败，文件不存在或已被删除'); 
        
        // 取出原文件后缀名，拼成alias值
        $explodedName = explode('.', $name);
        $name = array_shift($explodedName);
        $ext = $file->extend_info->ext;
        $name = "$name.$ext";

        // 更改
        $file->alias = $name;
        $file->save();

        // 更改成功
        return CustomCommon::makeSuccRes([], '重命名成功');
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
        ] = $request->input();

        // 文件名是否存在，同一父级下alias不允许重复
        $isExist = UploadFolder::select('id')
            ->where('fid', $fid)
            ->where('name', $name)
            ->first();

        // 重复
        if ($isExist && $isExist->id != $id) return CustomCommon::makeErrRes('文件名重复，请重新输入'); 

        // 名称可用
        
        // 实例化一个模型，限制两个条件，fid和id都相同时
        $folder = UploadFolder::where('fid', $fid)->where('id', $id)->first();

        // 文件夹不存在
        if ($folder == null) return CustomCommon::makeErrRes('修改失败，文件夹不存在或已被删除'); 

        // 更改
        $folder->name = $name;
        $folder->save();

        // 更改成功
        return CustomCommon::makeSuccRes([], '重命名成功');
    }
    

    /**
     * 复制文件或文件夹
     */
    public function copyResource (Request $request) {

        $res = diskController\PasetController::paset($request->input());
        
        if ($res === true) {
            return CustomCommon::makeSuccRes([], '复制成功');
        } else {
            return CustomCommon::makeErrRes($res);
        }

    }
    

    /**
     * 剪切文件或文件夹
     */
    public function cutResource (Request $request) {

        $res = diskController\PasetController::pasetCut($request->input());
        
        if ($res === true) {
            return CustomCommon::makeSuccRes([], '移动成功');
        } else {
            return CustomCommon::makeErrRes($res);
        }

    }


    // 删除文件或文件夹
    public function destroy (Request $request) {
        
        [
            'idList' => $idList
        ] = $request->input();
        
        
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


        // 如果有文件夹
        if (count($folderIdArr) > 0) {

            // 找出所有后代文件夹
            [
                'all' => $allFolder
            ] = diskController\PasetController::getOffspringFolder($folderIdArr);

            // 取出id列
            $allFolderId = array_column($allFolder, 'id');

            // 删除文件夹
            UploadFolder::destroy($allFolderId);

            // 删除所有后代文件
            UploadFile::whereIn('fid', $allFolderId)->delete();

        }

        // 如果有文件
        if (count($fileIdArr) > 0) {

            UploadFile::destroy($fileIdArr);

        }

        return CustomCommon::makeSuccRes([], '删除成功');
    }


    public function img (Request $request, $imgPath) {

        $disk = Storage::disk('uploadFiles');

        $filename = $disk->path($imgPath);

        // 设置内容类型
        $mime = image_type_to_mime_type(exif_imagetype($filename));
    
        $explodedMime = explode('/', $mime);
        $type = $explodedMime[1];
    
        // imageFn函数对应的压缩比
        $imageQuality = [
          'image/jpeg' => '50',
          'image/png' => '9',
          'image/webp' => '50',
          // 'image/gif' => '9',
        ];
    
        // 获取新的尺寸
        list($width, $height) = getimagesize($filename);
    
        // 图片大小
        $new_width = $width;
        $new_height = $height;
    
        // url是否有规定图片大小，固定宽高设置 > 缩放比设置
        $query = $request->input();
    
        // 是否有填写缩放比
        if (array_key_exists('percent', $query) && is_numeric($query['percent'])) {
          $percent = $query['percent'];
          $new_width = $width * $percent;
          $new_height = $height * $percent;
        }
    
        // 如果只填写了宽，没有高，则按照宽的缩放比例来处理
        if (array_key_exists('w', $query) && !array_key_exists('h', $query)) {
          $w = $query['w'];
          if (is_numeric(intval($w)) && intval($w) > 1) {
            $percent = $w / $new_width;
            $new_width = $new_width * $percent;
            $new_height = $new_height * $percent;
          }
        } else {
          if (array_key_exists('w', $query) && is_numeric($query['w']) && $query['w'] > 1) {
            $new_width = intval($query['w']);
          }
      
          if (array_key_exists('h', $query) && is_numeric($query['h']) && $query['h'] > 1) {
            $new_height = intval($query['h']);
          }
        }
    
        
        // 获取对应的图片处理函数
        $createFnName = "imagecreatefrom$type";

        $img = call_user_func($createFnName, $filename);        // 创建一个新图像
        imagesavealpha($img, true);                             // 保存透明色道
        $dim = imagecreatetruecolor($new_width, $new_height);  // 创建画布设定大小
        imagealphablending($dim, false);                       // 不用合并图像颜色，直接用$img的颜色替换$dim包括透明色
        imagesavealpha($dim, true);                            // 保存画布的透明色
        imagecopyresampled($dim, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
        // 输出
        $imageFnName = "image$type";
        $quality = $imageQuality[$mime];
        header("Content-Type: $mime");
        $content = call_user_func_array($imageFnName, [$dim, NULL, $quality]);
    }


    // 设置文件夹共享状态
    public function updateFolderShared (Request $request) {

        $status = $request->status;
        $fid = $request->fid;



    }
}
