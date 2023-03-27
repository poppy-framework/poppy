<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysAdContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sys_ad_content', function (Blueprint $table) {
            $table->increments('id')->comment('id');
            $table->string('title', 100)->default('')->comment('标题');
            $table->unsignedInteger('place_id')->default(0)->comment('位置 ID');
            $table->string('src', 255)->default('')->comment('地址');
            $table->string('introduce', 255)->default('')->comment('介绍');
            $table->dateTime('start_at')->nullable()->comment('开始时间');
            $table->dateTime('end_at')->nullable()->comment('开始时间');
            $table->string('action', 50)->default('')->comment('动作');
            $table->string('value', 255)->default('')->comment('动作值');
            $table->unsignedSmallInteger('list_order')->default(0)->comment('排序');
            $table->unsignedTinyInteger('is_enable')->default(0)->comment('0: 不显示, 1:显示');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_ad_content');
    }
}
