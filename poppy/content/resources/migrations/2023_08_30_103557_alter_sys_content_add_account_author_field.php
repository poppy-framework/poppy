<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSysContentAddAccountAuthorField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('sys_content', function (Blueprint $table) {
            $table->string('author', 100)->default('')->after('account_id')->comment('作者');
            $table->string('slug', 255)->default('')->after('title')->comment('友好访问名称');
            $table->string('keyword', 255)->default('')->after('slug')->comment('关键词');
            $table->string('description', 255)->default('')->after('keyword')->comment('描述');
            $table->dateTime('create_at')->default(null)->after('content')->comment('创建时间(展示用)');
            $table->index(['slug'], 'k_slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sys_content', function (Blueprint $table) {
            $table->dropColumn(['author', 'create_at', 'slug', 'keyword', 'description']);
            $table->dropIndex('k_slug');
        });
    }
}
