<?php

namespace Database\Factories;

use App\Models\UploadFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class UploadFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UploadFile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $ext = ['txt','jpg','jpeg','png'];
        $extVal = $ext[array_rand($ext)];
        return [
            'name' => md5($this->faker->name) . '.' . $extVal,
            'alias' => substr(md5($this->faker->name), 0, 8) . '.' . $extVal
        ];
    }
}
