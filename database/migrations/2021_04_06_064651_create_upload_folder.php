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
            $table->integer('fid')->unsigned();
            $table->string('name', 255);
            $table->integer('uid')->unsigned();
            $table->integer('uid_type')->unsigned();
            $table->bigInteger('ctime')->unsigned();
            $table->bigInteger('mtime')->unsigned();
            $table->bigInteger('dtime')->unsigned();
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
