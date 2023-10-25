<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trackings', function (Blueprint $table) {
            $table->id();
            $table->longText('previous_week_description')->nullable();
            $table->integer('question_id')->nullable();
            $table->string('answer')->nullable();
            $table->integer('weekly_rating')->nullable();
            $table->unsignedInteger('goal_id')->nullable();
            $table->foreign('goal_id')
                ->on('goals')
                ->references('id');
            $table->integer('goal_rating')->nullable();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
                ->on('users')
                ->references('id');
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
        Schema::dropIfExists('trackings');
    }
}
