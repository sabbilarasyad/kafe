<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lewati bila constraint unik (menu_id, bahan_id) sudah ada.
        if (Schema::hasIndex('resep_item', ['menu_id', 'bahan_id'], 'unique')) {
            return;
        }

        Schema::table('resep_item', function (Blueprint $table) {
            $table->unique(['menu_id', 'bahan_id'], 'resep_item_menu_bahan_unik');
        });
    }

    public function down(): void
    {
        if (Schema::hasIndex('resep_item', 'resep_item_menu_bahan_unik')) {
            Schema::table('resep_item', function (Blueprint $table) {
                $table->dropUnique('resep_item_menu_bahan_unik');
            });
        }
    }
};