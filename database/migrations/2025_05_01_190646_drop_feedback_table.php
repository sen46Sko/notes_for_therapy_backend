<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropFeedbackTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('feedback');
    }

    public function down()
    {

    }
}