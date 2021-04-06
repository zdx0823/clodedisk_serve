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
        return [
            'name' => md5($this->faker->name) . '.txt',
            'alias' => substr(md5($this->faker->name), 0, 8) . '.txt'
        ];
    }
}
