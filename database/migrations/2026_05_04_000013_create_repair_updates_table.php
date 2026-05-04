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
        Schema::create('repair_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_report_id')->constrained('damage_reports')->cascadeOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('update_type', 20)->default('note');
            $table->string('status_after', 20)->nullable();
            $table->string('result_summary')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('logged_at')->useCurrent()->index();
            $table->timestamps();

            $table->index(['damage_report_id', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_updates');
    }
};
