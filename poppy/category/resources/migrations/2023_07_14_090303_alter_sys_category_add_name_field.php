<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSysCategoryAddNameField extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sys_category', function (Blueprint $table) {
            $table->string('name', 50)->default('')->after('title')->comment('Name');
            $table->tinyInteger('is_enable')->default(1)->after('list_order')->comment('是否启用, 默认启用');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_category', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_enable']);
        });
    }
}
