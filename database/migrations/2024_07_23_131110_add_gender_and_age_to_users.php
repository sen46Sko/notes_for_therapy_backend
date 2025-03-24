<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenderAndAgeToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // gender should be null by default and can be male | female | other
            // age should be null by default and should be an integer
            $table->enum('gender', ['male' | 'female' | 'other'])->nullable()->default(null);
            $table->integer('age')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // drop the columns
            $table->dropColumn(['gender', 'age']);
        });
    }
}
