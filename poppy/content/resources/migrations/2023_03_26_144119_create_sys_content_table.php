<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sys_content', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 100)->default('')->comment('标题');
            $table->string('type', 40)->default('')->comment('分类[简易分类标识]');
            $table->integer('cat_id')->default(0)->comment('分类 ID');
            $table->string('thumb', 255)->default('')->comment('缩略图');
            $table->integer('list_order')->default(0)->comment('排序');
            $table->unsignedTinyInteger('is_enable')->default(0)->comment('是否启用');
            $table->text('content')->comment('内容');
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
        Schema::dropIfExists('sys_content');
    }
}
