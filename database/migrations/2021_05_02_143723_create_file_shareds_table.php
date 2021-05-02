<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileSharedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_shareds', function (Blueprint $table) {
            $table->id();
            $table->integer('file_id')->unsigned();
            $table->bigInteger('ctime');
        });

        Schema::table('file_shareds', function (Blueprint $table) {
            $table->foreign('file_id', 'file_shareds_file_id')
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
        Schema::table('file_shareds', function (Blueprint $table) {
            $table->dropForeign('file_shareds_file_id');
        });
        Schema::dropIfExists('file_shareds');
    }
}
