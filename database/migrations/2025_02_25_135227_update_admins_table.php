<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAdminsTable extends Migration
{
    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            // Adding new columns to the admins table
            if (!Schema::hasColumn('admins', 'status')) {
                $table->enum('status', ['active', 'pending'])->default('pending');
            }

            if (!Schema::hasColumn('admins', 'deactivate_to')) {
                $table->timestamp('deactivate_to')->nullable();
            }

            if (!Schema::hasColumn('admins', 'role')) {
                $table->enum('role', ['admin', 'super_admin', 'support', 'manager'])->default('admin');
            }

            if (!Schema::hasColumn('admins', 'avatar')) {
                $table->string('avatar')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn(['status', 'deactivate_to', 'role', 'avatar']);
        });
    }
};
