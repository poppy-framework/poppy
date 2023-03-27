<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysAdPlaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up():void
    {
        Schema::create('sys_ad_place', function (Blueprint $table) {
            $table->increments('id')->comment('id');
            $table->string('title', 100)->default('')->comment('广告位名称');
            $table->string('thumb', 200)->default('')->comment('广告位示意图');
            $table->string('introduce', 255)->default('')->comment('广告位介绍');
            $table->unsignedSmallInteger('width')->default(0)->comment('宽度');
            $table->unsignedSmallInteger('height')->default(0)->comment('高度');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down():void
    {
        Schema::dropIfExists('sys_ad_place');
    }
}
