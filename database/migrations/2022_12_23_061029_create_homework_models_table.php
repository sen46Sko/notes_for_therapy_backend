<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeworkModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('homework_models', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->longText('short_description');
            $table->string('file')->nullable();
            $table->longText('description')->nullable();
            $table->longText('thoughts')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('homework_models');
    }
}
