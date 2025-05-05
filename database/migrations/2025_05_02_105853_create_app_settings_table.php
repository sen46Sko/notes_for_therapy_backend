<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAppSettingsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('color')->default('#0095FF');
            $table->string('name')->default('Notes For Therapy');
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            'color' => '#0095FF',
            'name' => 'Notes For Therapy',
            'logo' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_settings');
    }
}