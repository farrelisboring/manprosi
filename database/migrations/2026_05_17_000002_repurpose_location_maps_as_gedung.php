<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_maps', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->change();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('location_map_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('location_maps')
                ->nullOnDelete();

            $table->index(['location_map_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['location_map_id', 'name']);
            $table->dropConstrainedForeignId('location_map_id');
        });

        Schema::table('location_maps', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable(false)->change();
        });
    }
};
