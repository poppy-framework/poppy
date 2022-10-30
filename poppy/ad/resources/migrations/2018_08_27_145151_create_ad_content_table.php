<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ad_content', function (Blueprint $table) {
            $table->increments('id')->comment('id');
            $table->string('title', 100)->default('')->comment('广告标题');
            $table->unsignedInteger('place_id')->default(0)->comment('广告位ID');
            $table->string('url', 200)->default('')->comment('链接地址');
            $table->string('introduce', 255)->default('')->comment('广告的介绍');
            $table->dateTime('end_at')->nullable()->comment('开始时间');
            $table->dateTime('start_at')->nullable()->comment('开始时间');
            $table->text('note')->default('')->comment('备注');
            $table->string('text_name', 100)->default('')->comment('文字广告名称');
            $table->string('text_url', 200)->default('')->comment('文字URL');
            $table->string('text_title', 100)->default('')->comment('文字广告title标题');
            $table->string('text_style', 50)->default('')->comment('文字广告的颜色');
            $table->string('image_src', 200)->default('')->comment('图片广告的图片地址');
            $table->string('image_url', 200)->default('')->comment('图片广告链接地址');
            $table->string('flash_src', 200)->default('')->comment('flash地址');
            $table->string('flash_url', 200)->default('')->comment('flash链接地址');
            $table->string('action', 50)->default('')->comment('动作');
            $table->unsignedTinyInteger('flash_loop')->default(1)->comment('flash循环次数');
            $table->unsignedSmallInteger('list_order')->default(0)->comment('排序');
            $table->unsignedTinyInteger('status')->default(0)->comment('0: 不显示, 1:显示');
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
        Schema::dropIfExists('ad_content');
    }
}
