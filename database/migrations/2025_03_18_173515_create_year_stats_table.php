<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYearStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('year_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('subscription_counter')->default(0);
            $table->integer('trial_counter')->default(0);
            $table->integer('cancle_counter')->default(0);
            $table->integer('monthly_plan')->default(0);
            $table->integer('yearly_plan')->default(0);
            $table->integer('total_users')->default(0);
            $table->integer('signups')->default(0);
            $table->integer('delete_account_counter')->default(0);
            $table->integer('resolved_tickets')->default(0);
            $table->integer('ticket_created')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('year_stats');
    }
}
