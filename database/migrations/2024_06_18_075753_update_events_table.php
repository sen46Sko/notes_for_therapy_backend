<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEventsTable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            // Dropping the old columns
            $table->dropColumn(['day', 'schedule_date', 'starts_at']);

            // Adding the new columns
            $table->date('date')->nullable();
            $table->time('start_at')->nullable();
            $table->time('end_at')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('low');
            $table->text('note')->nullable();
            $table->string('alert')->nullable()->default(null);
            $table->string('repeat')->nullable()->default(null);
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            // Dropping the new columns
            $table->dropColumn(['date', 'start_at', 'end_at', 'priority', 'note', 'alert', 'repeat']);

            // Adding the old columns back
            $table->date('day')->nullable();
            $table->datetime('schedule_date')->nullable();
            $table->varchar('starts_at', 255)->nullable();
        });
    }
}
