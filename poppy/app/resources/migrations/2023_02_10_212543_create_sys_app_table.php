<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysAppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sys_app', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('应用 ID');
            $table->string('title', 100)->default('')->comment('应用名称');
            $table->string('secret', 50)->default('')->comment('应用密钥');
            $table->string('name', 20)->default('')->comment('应用标识');
            $table->integer('account_id')->default(0)->comment('账号 ID');
            $table->string('account_type', 10)->default('')->comment('账号用户类型');
            $table->tinyInteger('is_enable')->default(1)->comment('是否启用');
            $table->string('note', 255)->default('')->comment('应用备注');
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
        Schema::dropIfExists('sys_app');
    }
}
