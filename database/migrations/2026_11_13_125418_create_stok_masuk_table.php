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
        Schema::create('stok_masuk', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('bahan_id')->constrained('bahan')->onDelete('cascade');
            $table->decimal('jumlah', 10, 2);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok_masuk');
        //drop stok_masuk table
    }
};
