<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UploadFile;
use DB;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UploadFileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 找出用户1顶层目录下的所有目录
        $fids = DB::table('upload_folder')
            ->whereRaw('`uid` = 1 AND `fid` is NOT NULL')
            ->pluck('id')
            ->toArray();

        // 拼成二维数组
        $fids = array_map(function ($id) {
            return ['fid' => $id];
        }, $fids);

        // 随机插入100条数据
        $files = UploadFile::factory()
            ->times(100)
            ->state(new Sequence(...$fids))
            ->create([
                UploadFile::CREATED_AT => time(),
                UploadFile::UPDATED_AT => time()
            ]);

        // 生成对应的file_extend数据
        $fileExtendData = array_map(function ($item) {

            $arr = explode('.', $item['alias']);
            return [
                'file_id' => $item['id'],
                'ext' => array_pop($arr),
                'size' => mt_rand(3145728, 20971520) // 3M 到 10M
            ];
            
        }, $files->toArray());

        // 插入file_extend数据
        DB::table('upload_file_extend')->insert($fileExtendData);
    }
}
