<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFolderSharedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folder_shareds', function (Blueprint $table) {
            $table->id();
            $table->integer('fid')->unsigned();
            $table->bigInteger('ctime');
        });

        Schema::table('folder_shareds', function (Blueprint $table) {
            $table->foreign('fid', 'folder_shareds_fid')
                ->references('id')
                ->on('upload_folder')
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
        Schema::table('folder_shareds', function (Blueprint $table) {
            $table->dropForeign('folder_shareds_fid');
        });
        Schema::dropIfExists('folder_shareds');
    }
}
