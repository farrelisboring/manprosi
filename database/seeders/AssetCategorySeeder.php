<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use Illuminate\Database\Seeder;

class AssetCategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [
            [
                'code' => 'BAHAN_MEDIS_HABIS_PAKAI',
                'name' => 'Bahan Medis Habis Pakai',
                'description' => 'Kategori bahan medis sekali pakai dan consumable medis',
            ],
            [
                'code' => 'FARMASI_DAN_OBAT',
                'name' => 'Farmasi & Obat-obatan',
                'description' => 'Kategori obat-obatan dan produk farmasi',
            ],
            [
                'code' => 'PERALATAN_MEDIS',
                'name' => 'Peralatan Medis',
                'description' => 'Kategori alat dan perangkat medis',
            ],
            [
                'code' => 'PERLENGKAPAN_BEDAH_DAN_RUANG_OPERASI',
                'name' => 'Perlengkapan Bedah & Ruang Operasi',
                'description' => 'Kategori perlengkapan operasi dan bedah',
            ],
            [
                'code' => 'PERLENGKAPAN_DIAGNOSTIK_DAN_LABORATORIUM',
                'name' => 'Perlengkapan Diagnostik & Laboratorium',
                'description' => 'Kategori alat dan bahan laboratorium',
            ],
            [
                'code' => 'MATERIAL_PENCITRAAN_DAN_RADIOLOGI',
                'name' => 'Material Pencitraan & Radiologi',
                'description' => 'Kategori perlengkapan radiologi dan imaging',
            ],
            [
                'code' => 'PERLENGKAPAN_PERAWATAN_PASIEN_DAN_RUANG_RAWAT',
                'name' => 'Perlengkapan Perawatan Pasien & Ruang Rawat',
                'description' => 'Kategori kebutuhan perawatan pasien',
            ],
            [
                'code' => 'PERLENGKAPAN_GAWAT_DARURAT_DAN_PERAWATAN_KRITIS',
                'name' => 'Perlengkapan Gawat Darurat & Perawatan Kritis',
                'description' => 'Kategori perlengkapan emergency dan ICU',
            ],
            [
                'code' => 'PERLENGKAPAN_STERILISASI_DAN_PENGENDALIAN_INFEKSI',
                'name' => 'Perlengkapan Sterilisasi & Pengendalian Infeksi',
                'description' => 'Kategori sterilisasi dan infection control',
            ],
            [
                'code' => 'PERLENGKAPAN_PEMELIHARAAN_FASILITAS_DAN_TEKNIK',
                'name' => 'Perlengkapan Pemeliharaan Fasilitas & Teknik',
                'description' => 'Kategori maintenance fasilitas dan engineering',
            ],
            [
                'code' => 'INFRASTRUKTUR_TI_DAN_DIGITAL',
                'name' => 'Infrastruktur TI & Digital',
                'description' => 'Kategori perangkat IT dan infrastruktur digital',
            ],
            [
                'code' => 'LAYANAN_MAKANAN_DAN_NUTRISI',
                'name' => 'Layanan Makanan & Nutrisi',
                'description' => 'Kategori layanan makanan dan nutrisi rumah sakit',
            ],
            [
                'code' => 'PERLENGKAPAN_LAUNDRY_DAN_HOUSEKEEPING',
                'name' => 'Perlengkapan Laundry & Housekeeping',
                'description' => 'Kategori laundry dan housekeeping',
            ],
            [
                'code' => 'PERLENGKAPAN_ADMINISTRASI_DAN_KANTOR',
                'name' => 'Perlengkapan Administrasi & Kantor',
                'description' => 'Kategori kebutuhan administrasi dan kantor',
            ],
            [
                'code' => 'PENGELOLAAN_LIMBAH_MEDIS',
                'name' => 'Pengelolaan Limbah Medis',
                'description' => 'Kategori pengelolaan limbah biomedis',
            ],
            [
                'code' => 'PERLENGKAPAN_BANK_DARAH_DAN_TRANSFUSI',
                'name' => 'Perlengkapan Bank Darah & Transfusi',
                'description' => 'Kategori bank darah dan transfusi',
            ],
            [
                'code' => 'PERALATAN_KEAMANAN_DAN_KESELAMATAN',
                'name' => 'Peralatan Keamanan & Keselamatan',
                'description' => 'Kategori keamanan dan keselamatan',
            ],
            [
                'code' => 'FURNITUR_DAN_ASET_TETAP',
                'name' => 'Furnitur & Aset Tetap',
                'description' => 'Kategori furnitur dan aset tetap',
            ],
            [
                'code' => 'UTILITAS_DAN_INFRASTRUKTUR_BAHAN_HABIS_PAKAI',
                'name' => 'Utilitas & Infrastruktur Bahan Habis Pakai',
                'description' => 'Kategori utilitas dan infrastruktur consumable',
            ],
            [
                'code' => 'INVENTARIS_DEPARTEMEN_KHUSUS',
                'name' => 'Inventaris Departemen Khusus',
                'description' => 'Kategori inventaris khusus per departemen',
            ],
        ];

        foreach ($categories as $category) {
            AssetCategory::query()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ],
            );
        }
    }
}
