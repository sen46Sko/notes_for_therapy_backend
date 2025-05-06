<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained()->onDelete('cascade');
            $table->string('device_type'); // pc, mobile, tablet
            $table->string('device_name')->nullable();
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->timestamp('last_active_at');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_devices');
    }
};