<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveLanguageAndDateFormatFromAppSettings extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('app_settings', 'language')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->dropColumn('language');
            });
        }

        if (Schema::hasColumn('app_settings', 'date_format')) {
            Schema::table('app_settings', function (Blueprint $table) {
                $table->dropColumn('date_format');
            });
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->string('language')->default('English');
            $table->string('date_format')->default('MM/DD/YYYY');
        });
    }
}