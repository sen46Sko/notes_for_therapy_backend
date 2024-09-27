<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDateToTimestampsInEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            // change type of columns date, start_at and end_at to timestamps
            $table->dropColumn(['date', 'start_at', 'end_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            // change type of columns date, start_at and end_at to date
            $table->date('date');
            $table->date('start_at');
            $table->date('end_at');
        });
    }
}
