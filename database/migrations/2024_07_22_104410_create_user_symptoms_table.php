<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSymptomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_symptoms', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->timestamp('date');
            $table->enum('intensity', ['mild', 'moderate', 'intense', 'acute']);
            $table->text('note')->nullable();

            $table->unsignedInteger('symptom_id')->nullable();
            $table->foreign('symptom_id')
                ->on('symptoms')
                ->references('id');

            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')
                ->on('users')
                ->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_symptoms');
    }
}
