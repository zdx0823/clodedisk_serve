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
        UploadFile::factory()
            ->times(100)
            ->state(new Sequence(...$fids))
            ->create([
                UploadFile::CREATED_AT => time(),
                UploadFile::UPDATED_AT => time()
            ]);
    }
}
