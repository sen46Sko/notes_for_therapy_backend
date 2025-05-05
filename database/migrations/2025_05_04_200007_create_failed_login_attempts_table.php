<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('failed_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('device_type');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->timestamp('attempted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('failed_login_attempts');
    }
};