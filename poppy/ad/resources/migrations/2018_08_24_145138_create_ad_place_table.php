<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdPlaceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_place', function (Blueprint $table) {
            $table->increments('id')->comment('id');
            $table->string('title', 100)->default('')->comment('广告位名称');
            $table->string('thumb', 200)->default('')->comment('广告位示意图');
            $table->string('introduce', 255)->default('')->comment('广告位介绍');
            $table->unsignedSmallInteger('width')->default(0)->comment('宽度');
            $table->unsignedSmallInteger('height')->default(0)->comment('高度');
            $table->dateTime('created_at')->nullable()->comment('创建时间');
            $table->dateTime('updated_at')->nullable()->comment('修改时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ad_place');
    }
}
