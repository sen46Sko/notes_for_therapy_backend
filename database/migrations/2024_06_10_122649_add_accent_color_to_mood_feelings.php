<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccentColorToMoodFeelings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mood_feelings', function (Blueprint $table) {
            // Add accent_color
            $table->string('color')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mood_feelings', function (Blueprint $table) {
            //
            $table->dropColumn('color');
        });
    }
}
