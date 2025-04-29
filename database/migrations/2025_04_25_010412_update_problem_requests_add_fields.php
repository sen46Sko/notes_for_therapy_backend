<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProblemRequestsAddFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('problem_requests', function (Blueprint $table) {
            $table->enum('status', ['in_progress', 'waiting', 'resolved', 'closed'])->default('in_progress');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();

            $table->foreign('assign_to')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problem_requests', function (Blueprint $table) {
            $table->dropForeign(['assign_to']);
            $table->dropColumn(['status', 'note', 'assign_to']);
        });
    }
}
