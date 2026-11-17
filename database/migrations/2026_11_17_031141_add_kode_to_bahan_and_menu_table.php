<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan', function (Blueprint $table) {
            $table->string('kode', 20)->nullable()->unique()->after('id');
        });

        Schema::table('menu', function (Blueprint $table) {
            $table->string('kode', 20)->nullable()->unique()->after('id');
        });
    }

    
    public function down(): void
    {
        Schema::table('bahan', function (Blueprint $table) {
                $table->dropUnique(['kode']);
            $table::dropClomn('kode');
        });

        Schema::table('menu', function (Blueprint $table) {
                $table->dropUnique(['kode']);
            $table->dropColumn('kode');
        });
    }
};
