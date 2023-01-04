<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 100)->default('')->comment('标题');
            $table->integer('parent_id')->default(0)->comment('父级 ID');
            $table->integer('top_id')->default(0)->comment('顶级 ID');
            $table->string('type', 40)->default('')->comment('类型');
            $table->integer('list_order')->default(0)->comment('排序');
            $table->timestamps();
            $table->index('type', 'k_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_category');
    }
}
