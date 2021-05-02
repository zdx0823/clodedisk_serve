<?php

namespace Database\Factories;

use App\Models\UploadFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

class UploadFolderFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UploadFolder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'fid' => null,
            'name' => \Faker\Factory::create('zh_CN')->name,
            'uid' => 1,
        ];
    }
}
