<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoodFeelingsTable extends Migration
{
    public function up()
    {
        Schema::create('mood_feelings', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mood_feelings');
    }
}
