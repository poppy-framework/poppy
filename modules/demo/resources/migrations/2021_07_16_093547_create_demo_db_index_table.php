<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemoDbIndexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demo_db_index', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('age')->default(0)->comment('Age');
            $table->string('department')->default('')->comment('部门');
            $table->year('birth_year')->default(null)->comment('出生年份');
            $table->string('truename')->default('')->comment('姓名');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demo_db_index');
    }
}
