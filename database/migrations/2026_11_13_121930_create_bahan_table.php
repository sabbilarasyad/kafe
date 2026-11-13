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
        Schema::create('bahan', function (Blueprint $table) {
            //create bahan table with columns id, nama, nama_kunci, satuan, stok, batas_minimum, harga_per_satuan, harga_diperbarui, created_at, updated_at
            $table->id();
            $table->string('nama');
            $table->string('nama_kunci')->unique();
            $table->string('satuan', 20);
            $table->decimal('stok', 10, 2)->default(0);
            $table->decimal('batas_minimum', 10, 2)->default(0);
            $table->decimal('harga_per_satuan', 15, 2)->nullable();
            $table->timestamp('harga_diperbarui')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan');
        //drop bahan table
    }
};
