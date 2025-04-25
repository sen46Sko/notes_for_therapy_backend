<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProblemLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('problem_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->enum('action_type', ['created', 'reviewed', 'admin_responded', 'user_responded', 'status_updated', 'closed']);
            $table->string('description');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('ticket_id')->references('id')->on('problem_requests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_logs');
    }
}
