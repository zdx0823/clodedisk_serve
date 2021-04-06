<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadFolder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_folder', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fid')->unsigned()->nullable();
            $table->string('name', 255);
            $table->integer('uid')->unsigned();
            $table->integer('uid_type')->unsigned();
            $table->bigInteger('ctime')->unsigned();
            $table->bigInteger('mtime')->unsigned()->default(0);
            $table->bigInteger('dtime')->unsigned()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upload_folder');
    }
}
