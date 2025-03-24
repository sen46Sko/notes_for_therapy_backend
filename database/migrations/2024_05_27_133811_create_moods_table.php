<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoodsTable extends Migration
{
    public function up()
    {
        Schema::create('moods', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('value');
            $table->enum('type', ['momentary', 'daily']);
            $table->unsignedInteger('mood_relation_id')->nullable();
            $table->foreign('mood_relation_id')
                ->on('mood_relations')
                ->references('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->on('users')
                ->references('id');
            $table->text('note')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('moods');
    }
}
