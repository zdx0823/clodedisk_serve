<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyUploadFolder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upload_folder', function (Blueprint $table) {
            $table->foreign('fid', 'upload_folder_fid')->references('id')->on('upload_folder')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upload_folder', function (Blueprint $table) {
            $table->dropForeign('upload_folder_fid');
        });
    }
}
