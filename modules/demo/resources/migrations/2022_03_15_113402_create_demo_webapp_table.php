<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemoWebappTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demo_webapp', function (Blueprint $table) {
            $table->integer('id', true);

            /* 日期
             * ---------------------------------------- */
            $table->dateTime('birth_at')->nullable();
            $table->date('birth_date')->nullable();
            $table->dateTime('post_at')->nullable();
            $table->dateTime('modify_at')->nullable();
            $table->dateTime('delete_at')->nullable();
            $table->dateTime('rename_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();

            /* 内容
             * ---------------------------------------- */
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('note')->nullable();
            $table->string('setting', 255)->nullable();
            $table->text('content')->nullable();

            /* 用户
             * ---------------------------------------- */
            $table->string('email')->nullable();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->integer('age')->nullable()->default(0);
            $table->integer('score')->nullable()->default(0);

            /* 自定义排序 | 样式
             * ---------------------------------------- */
            $table->integer('sort')->default(0);
            $table->string('style')->default('');

            /* 状态 / 0|1
             * ---------------------------------------- */
            $table->integer('status')->nullable();
            $table->integer('progress')->nullable();


            $table->tinyInteger('loading')->nullable();
            $table->tinyInteger('is_open')->nullable();
            $table->tinyInteger('trashed')->nullable();
            $table->tinyInteger('is_enable')->nullable()->default(1);

            /* File | Image | Link | Images
             * ---------------------------------------- */
            $table->string('file')->nullable();
            $table->string('files')->nullable();
            $table->string('pdf')->nullable();
            $table->string('video')->nullable();
            $table->string('audio')->nullable();
            $table->string('image')->nullable();
            $table->string('images')->nullable();
            $table->string('link')->nullable();

            /* 连表
             * ---------------------------------------- */
            $table->integer('account_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demo_webapp');
    }
}
