<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameExpertTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_expert', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 60)->default('')->comment('名称');
            $table->string('game', 60)->default('')->comment('游戏');
            $table->string('nickname', 255)->default('')->comment('昵称');
            $table->string('picture', 255)->default('')->comment('图片');
            $table->dateTime('updated_at')->nullable()->comment('修改时间');
            $table->dateTime('created_at')->nullable()->comment('创建时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_expert');
    }
}
