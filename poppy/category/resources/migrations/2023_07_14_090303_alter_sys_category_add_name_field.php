<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSysCategoryAddNameField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('sys_category', function (Blueprint $table) {
            $table->string('name', 50)->default('')->after('title')->comment('Name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sys_category', function (Blueprint $table) {
            $table->dropColumn(['name']);
        });
    }
}
