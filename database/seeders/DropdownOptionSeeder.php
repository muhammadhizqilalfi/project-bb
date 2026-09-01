<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DropdownOption;

class DropdownOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            ['category' => 'kategori_pidana', 'label' => 'KAMNEGTIBUM DAN TPUL', 'form_target' => 'Keduanya'],
            ['category' => 'kategori_pidana', 'label' => 'NARKOTIKA DAN ZAT ADITIF LAINNYA', 'form_target' => 'Keduanya'],
            ['category' => 'kategori_pidana', 'label' => 'OHARDA', 'form_target' => 'Keduanya'],
            ['category' => 'kategori_pidana', 'label' => 'TERORIS', 'form_target' => 'Keduanya'],
            ['category' => 'kategori_pidana', 'label' => 'KORUPSI', 'form_target' => 'Keduanya'],

            ['category' => 'jenis_narkotika', 'label' => 'Sabu', 'form_target' => 'Keduanya'],
            ['category' => 'jenis_narkotika', 'label' => 'Ganja', 'form_target' => 'Keduanya'],
            ['category' => 'jenis_narkotika', 'label' => 'Ekstasi / Pil', 'form_target' => 'Keduanya'],
            ['category' => 'jenis_narkotika', 'label' => 'Heroin', 'form_target' => 'Keduanya'],
            ['category' => 'jenis_narkotika', 'label' => 'Tembakau Sintetis', 'form_target' => 'Keduanya'],
            ['category' => 'jenis_narkotika', 'label' => 'Obat Keras', 'form_target' => 'Keduanya'],
            ['category' => 'jenis_narkotika', 'label' => 'Lainnya', 'form_target' => 'Keduanya'],
            
            ['category' => 'satuan', 'label' => 'Gram (g)', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Miligram (mg)', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Kilogram (Kg)', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Milliliter (ml)', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Liter (L)', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Lembar', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Box', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Unit', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Paket', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Pcs', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Buah', 'form_target' => 'Keduanya'],

            ['category' => 'tempat_penyimpanan', 'label' => 'Gudang Barang Bukti Kejaksaan Negeri Banda Aceh', 'form_target' => 'Keduanya'],
            ['category' => 'tempat_penyimpanan', 'label' => 'RUPBASAN', 'form_target' => 'Keduanya'],
            ['category' => 'tempat_penyimpanan', 'label' => 'KEJATI', 'form_target' => 'Keduanya'],

            ['category' => 'keterangan_tahap', 'label' => 'Tahap Persidangan', 'form_target' => '3A'],
            ['category' => 'keterangan_tahap', 'label' => 'Tahap II', 'form_target' => '3A'],
            ['category' => 'keterangan_tahap', 'label' => 'Tahap Pelimpahan', 'form_target' => '3A'],

            // Dropdown Opsi Form 3D
            ['category' => 'status_lelang', 'label' => 'LAKU', 'form_target' => '3D'],
            ['category' => 'status_lelang', 'label' => 'BELUM_LAKU', 'form_target' => '3D'],
            ['category' => 'status_lelang', 'label' => 'PROSES', 'form_target' => '3D'],
            ['category' => 'instansi_penilai', 'label' => 'KPKNL', 'form_target' => '3D'],
            ['category' => 'instansi_penilai', 'label' => 'KJPP', 'form_target' => '3D'],

            // Dropdown Opsi Form 3E & 3F
            ['category' => 'jenis_lelang', 'label' => 'Lelang Eksekusi Rampasan', 'form_target' => '3E'],
            ['category' => 'metode_penjualan', 'label' => 'Penjualan Langsung PPA', 'form_target' => '3F'],
        ];

        foreach ($options as $opt) {
            DropdownOption::create($opt);
        }
    }
}