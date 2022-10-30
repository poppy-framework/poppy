<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemoDbTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demo_db', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('tiny_integer')->default(0)->comment('TinyInt');
            $table->unsignedInteger('u_integer')->default(0)->comment('Unsigned Integer');
            $table->string('var_char_20', 20)->default('')->comment('Var Char 20');
            $table->char('char_20', 20)->default('')->comment('Char 20');
            $table->text('text')->default('')->comment('Text');
            $table->decimal('decimal')->default('0.00')->comment('Decimal');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demo_db');
    }
}
