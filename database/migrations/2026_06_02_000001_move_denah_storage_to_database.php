<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE locations ADD denah_image_data MEDIUMBLOB NULL AFTER description');
            DB::statement('ALTER TABLE locations ADD denah_image_mime_type VARCHAR(255) NULL AFTER denah_image_data');
            DB::statement('ALTER TABLE locations DROP COLUMN denah_image_path');

            return;
        }

        Schema::table('locations', function (Blueprint $table) {
            $table->binary('denah_image_data')->nullable()->after('description');
            $table->string('denah_image_mime_type')->nullable()->after('denah_image_data');
            $table->dropColumn('denah_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE locations ADD denah_image_path VARCHAR(255) NULL AFTER description');
            DB::statement('ALTER TABLE locations DROP COLUMN denah_image_data');
            DB::statement('ALTER TABLE locations DROP COLUMN denah_image_mime_type');

            return;
        }

        Schema::table('locations', function (Blueprint $table) {
            $table->string('denah_image_path')->nullable()->after('description');
            $table->dropColumn(['denah_image_data', 'denah_image_mime_type']);
        });
    }
};
