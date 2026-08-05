<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DropdownOption;

class DropdownOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // Kategori Tindak Pidana
            ['category' => 'kategori_pidana', 'label' => 'KAMNEGTIBUM DAN TPUL', 'form_target' => '3A'],
            ['category' => 'kategori_pidana', 'label' => 'NARKOTIKA DAN ZAT ADITIF LAINNYA', 'form_target' => 'Keduanya'],
            ['category' => 'kategori_pidana', 'label' => 'OHARDA', 'form_target' => '3A'],
            ['category' => 'kategori_pidana', 'label' => 'TERORIS', 'form_target' => '3A'],
            ['category' => 'kategori_pidana', 'label' => 'KORUPSI', 'form_target' => 'Keduanya'],

            // Jenis Narkotika
            ['category' => 'jenis_narkotika', 'label' => 'Sabu', 'form_target' => '3A'],
            ['category' => 'jenis_narkotika', 'label' => 'Ganja', 'form_target' => '3A'],
            ['category' => 'jenis_narkotika', 'label' => 'Ekstasi / Pil', 'form_target' => '3A'],
            ['category' => 'jenis_narkotika', 'label' => 'Heroin', 'form_target' => '3A'],
            ['category' => 'jenis_narkotika', 'label' => 'Tembakau Sintetis', 'form_target' => '3A'],
            ['category' => 'jenis_narkotika', 'label' => 'Obat Keras', 'form_target' => '3A'],
            ['category' => 'jenis_narkotika', 'label' => 'Lainnya', 'form_target' => '3A'],

            // Satuan
            ['category' => 'satuan', 'label' => 'Gram', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Kilogram (Kg)', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Milliliter (ml)', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Liter (L)', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Unit', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Paket', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Pcs', 'form_target' => 'Keduanya'],
            ['category' => 'satuan', 'label' => 'Buah', 'form_target' => 'Keduanya'],

            // Tempat Penyimpanan
            ['category' => 'tempat_penyimpanan', 'label' => 'Gudang Barang Bukti Kejaksaan Negeri Banda Aceh', 'form_target' => 'Keduanya'],
            ['category' => 'tempat_penyimpanan', 'label' => 'RUPBASAN', 'form_target' => 'Keduanya'],

            // Keterangan Tahap
            ['category' => 'keterangan_tahap', 'label' => 'Tahap Persidangan', 'form_target' => '3A'],
            ['category' => 'keterangan_tahap', 'label' => 'Tahap II', 'form_target' => '3A'],
            ['category' => 'keterangan_tahap', 'label' => 'Tahap Pelimpahan', 'form_target' => '3A'],
        ];

        foreach ($options as $opt) {
            DropdownOption::create($opt);
        }
    }
}