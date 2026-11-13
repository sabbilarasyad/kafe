<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //migrate role and nomor_hp columns to users table
            $table->string('role')->default('user');
            $table->string('nomor_hp')->nullable()->unique();
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //drop role and nomor_hp columns from users table
            $table->dropColumn(['role', 'nomor_hp', 'wajib_ganti_password']);
        });
    }
};
