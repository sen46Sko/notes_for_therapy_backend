<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('system_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->datetime('release_date');
            $table->string('git_commit')->nullable();
            $table->string('description')->nullable();
            $table->text('changelog')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_versions');
    }
};