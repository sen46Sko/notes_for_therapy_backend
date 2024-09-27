<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoodMoodFeelingsTable extends Migration
{
    public function up()
    {
        Schema::create('moods_mood_feelings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mood_id')->nullable();
            $table->foreign('mood_id')
                ->on('moods')
                ->references('id');
            $table->unsignedInteger('mood_feeling_id')->nullable();
            $table->foreign('mood_feeling_id')
                ->on('mood_feelings')
                ->references('id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('moods_mood_feelings');
    }
}
