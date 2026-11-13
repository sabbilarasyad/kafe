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
        Schema::create('pesanan_bahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->restrictOnDelete();
            $table->foreignId('bahan_id')->constrained('bahan')->restrictOnDelete();
            $table->decimal('jumlah', 10, 2);
            $table->timestamps();
            $table->unique(['pesanan_id', 'bahan_id']); // Ensure unique combination of pesanan_id and bahan_id  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_bahan');
        //drop pesanan_bahan table
    }
};
