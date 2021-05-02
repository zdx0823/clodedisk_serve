<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UploadFolder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use DB;

class UploadFolderSeeder extends Seeder
{

    private static function buildUser ($userBaseId, $uid) {

        $_name = "用户_" . ($uid - 1) . "的顶层目录";
        // 生成用户1的顶层目录取出id
        $user_1 = UploadFolder::factory()->times(1)->create([
            'fid' => $userBaseId,
            'name' => $_name,
            'uid' => $uid,
            UploadFolder::CREATED_AT => time(),
            UploadFolder::UPDATED_AT => time()
        ])->first()->id;

        // 在顶层目录下插入十条数据
        $users = UploadFolder::factory()
            ->times(10)
            ->create([
                'uid' => $uid,
                'fid' => $user_1,
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
                'uid' => $uid,
                UploadFolder::CREATED_AT => time(),
                UploadFolder::UPDATED_AT => time()
            ]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // 生成管理员目录
        $baseId = UploadFolder::factory()->times(1)->create([
            'name' => 'root',
            UploadFolder::CREATED_AT => time(),
            UploadFolder::UPDATED_AT => time()
        ])->first()->id;

        // 生成所有用户目录
        $userBaseId = UploadFolder::factory()->times(1)->create([
            'fid' => $baseId,
            'name' => '所有用户',
            UploadFolder::CREATED_AT => time(),
            UploadFolder::UPDATED_AT => time()
        ])->first()->id;

        // 生成3个用户的顶层文件夹
        self::buildUser($userBaseId, 2, 3);
        self::buildUser($userBaseId, 3, 3);
        self::buildUser($userBaseId, 4, 3);
    }
}
