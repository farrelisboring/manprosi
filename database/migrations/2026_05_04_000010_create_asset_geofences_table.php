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
        Schema::create('asset_geofences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('asset_id')->nullable()->constrained('assets')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('rule_type', 30)->default('must_stay_inside');
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_geofences');
    }
};
