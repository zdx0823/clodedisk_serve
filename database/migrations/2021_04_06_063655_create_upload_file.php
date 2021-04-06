<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadFile extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_file', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('alias', 255);
            $table->integer('fid')->unsigned();
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
        Schema::dropIfExists('upload_file');
    }
}
