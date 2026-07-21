<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSysContentAddAccountIdField extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sys_content', function (Blueprint $table) {
            $table->integer('account_id')->default(0)->after('cat_id')->comment('Account Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_content', function (Blueprint $table) {
            $table->dropColumn(['account_id']);
        });
    }
}
