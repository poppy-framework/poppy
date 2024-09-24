<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentToSysConfigTable extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::table('sys_config', function (Blueprint $table) {
            $table->text('content')->nullable()->comment('值的JSON版本');
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::table('sys_config', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
}
