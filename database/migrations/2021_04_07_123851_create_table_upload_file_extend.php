<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUploadFileExtend extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_file_extend', function (Blueprint $table) {
            $table->increments('id');
            $table->text('size');
            $table->string('ext');
            $table->integer('file_id')->unsigned();
        });

        Schema::table('upload_file_extend', function (Blueprint $table) {
            $table->foreign('file_id', 'upload_file_extend_file_id')
                ->references('id')
                ->on('upload_file')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upload_file_extend', function (Blueprint $table) {
            $table->dropForeign('upload_file_extend_file_id');
        });
        Schema::dropIfExists('upload_file_extend');
    }
}
