<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UploadFolder;
use App\Models\UploadFile;

class DiskController extends Controller
{

    private function doRes ($data = [], $msg = '操作成功') {
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
        do {

            $crumbOne = UploadFolder::select(['id', 'fid', 'name'])->where('id', $curFid)->first();
            $curFid = $crumbOne->fid;
            array_unshift($data, $crumbOne->toArray());

        } while ($curFid !== NULL);
        
        $data[0]['fid'] = 0;
        $data[0]['name'] = '全部文件';

        $path = implode('/', array_column($data, 'name'));
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


    // 文件，文件夹列表
    public function show (Request $request) {
        $uid = 1;
        $uid_type = 3;
        $fid = $request->fid;
        $offset = ($request->page - 1) * $request->pagesize;
        $limit = $request->pagesize;
        $order = $request->order;

        // 如果fid==0表示要获取用户顶层目录下的文件，取出该用户的顶层目录id
        if ($fid == 0) {
            $fid = UploadFolder::select(['id'])
                ->where('uid_type', $uid_type)
                ->where('uid', $uid)
                ->where('fid', null)
                ->first()->id;
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

        return $this->doRes([
            'data' => $data,
            'crumbData' => $crumb,
            'fid' => $fid,
            'path' => $tPath
        ], '获取成功');
    }

    // 新建文件夹
    public function storeFolder (Request $request) {
        return 'storeFolder';
    }

    // 上传文件
    public function upload (Request $request) {
        return 'upload';
    }

    // 修改文件名
    public function updateFileName (Request $request) {
        return 'updateFileName';
    }
    
    // 修改文件夹名
    public function updateFolderName (Request $request) {
        return 'updateFolderName';
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
