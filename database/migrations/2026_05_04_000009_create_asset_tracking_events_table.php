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
        Schema::create('asset_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('reader_identifier')->nullable();
            $table->string('source', 20)->default('rfid')->index();
            $table->string('event_type', 30)->index();
            $table->string('raw_tag')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('detected_at')->useCurrent()->index();
            $table->timestamps();

            $table->index(['asset_id', 'detected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_tracking_events');
    }
};
