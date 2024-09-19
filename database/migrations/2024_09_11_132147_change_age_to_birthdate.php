<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAgeToBirthdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add birthdate column of type date after column 'age' and remove 'age' column
            $table->date('birthdate')->nullable()->after('age');
            $table->dropColumn('age');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('birthdate', function (Blueprint $table) {
            // Add age column of type integer after column 'birthdate' and remove 'birthdate' column
            $table->integer('age')->after('birthdate');
            $table->dropColumn('birthdate');
        });
    }
}
