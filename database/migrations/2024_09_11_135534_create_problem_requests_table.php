<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProblemRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('problem_requests', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->foreignId('problem_id')->nullable()->constrained('problems');
            $table->string('problem_description');
            $table->string('email');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('problem_requests');
    }
}
