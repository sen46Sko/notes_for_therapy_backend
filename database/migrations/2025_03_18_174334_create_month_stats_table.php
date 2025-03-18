<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('month_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('date');
            $table->integer('subscription_counter');
            $table->integer('trial_counter');
            $table->integer('cancle_counter');
            $table->integer('monthly_plan');
            $table->integer('yearly_plan');
            $table->integer('total_users');
            $table->integer('signups');
            $table->integer('delete_account_counter');
            $table->integer('resolved_tickets');
            $table->integer('ticket_created');
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
        Schema::dropIfExists('month_stats');
    }
}
