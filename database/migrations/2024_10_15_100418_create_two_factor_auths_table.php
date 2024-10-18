<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwoFactorAuthsTable extends Migration
{
    public function up()
    {
        Schema::create('two_factor_auths', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')
               ->on('users')
               ->references('id')
               ->onDelete('cascade');

            $table->enum('type', ['email'])->default('email');
            $table->string('value');
            $table->string('verification_code')->nullable();
            $table->timestamp('code_expires_at')->nullable();
            $table->boolean('is_enabled')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('two_factor_auths');
    }
}
