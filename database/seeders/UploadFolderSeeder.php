<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UploadFolder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use DB;

class UploadFolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // 生成用户1的顶层目录取出id
        $baseId = UploadFolder::factory()->times(1)->create([
            'name' => '用户_1的顶层目录',
            UploadFolder::CREATED_AT => time(),
            UploadFolder::UPDATED_AT => time()
        ])->first()->id;

        // 在顶层目录下插入十条数据
        $users = UploadFolder::factory()
            ->times(10)
            ->create([
                'fid' => $baseId,
            ]);

        // 取出10条数据的id
        $user_ids = $users->pluck('id')->toArray();
        $user_ids = array_map(function ($id) {
            return ['fid' => $id];
        }, $user_ids);

        // 在十条数据下随机再插入20条数据
        UploadFolder::factory()
            ->times(20)
            ->state(new Sequence(...$user_ids))
            ->create([
                UploadFolder::CREATED_AT => time(),
                UploadFolder::UPDATED_AT => time()
            ]);
    }
}
