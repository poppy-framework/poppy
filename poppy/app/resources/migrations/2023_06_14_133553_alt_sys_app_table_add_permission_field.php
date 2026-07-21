<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AltSysAppTableAddPermissionField extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sys_app', function (Blueprint $table) {
            $table->text('permissions')->after('account_id')->comment('用户权限 KEY, 使用 `,` 分隔');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_app', function (Blueprint $table) {
            $table->dropColumn(['permissions']);
        });
    }
}
