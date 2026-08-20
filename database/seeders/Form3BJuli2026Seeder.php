<?php

namespace Database\Seeders;

use App\Models\FormTemplate;
use Illuminate\Database\Seeder;

class Form3BJuli2026Seeder extends Seeder
{
    public function run(): void
    {
        // Menyimpan data rekapitulasi Form 3B Juli 2026 per jenis tindak pidana
        FormTemplate::updateOrCreate(
            [
                'form_type' => '3B',
                'month'     => 7,
                'year'      => 2026,
            ],
            [
                'name'  => 'FORM 3B Juli 2026',
                'cases' => [
                    [
                        'satuanKerja'          => 'Kejari Banda Aceh',
                        'kategoriTindakPidana' => 'Teroris',
                        'sisaBulanLalu'        => 3,
                        'masukBulanLaporan'    => 0,
                        'jumlahBulanLaporan'   => 3,
                        'sisaBulanLaporan'     => 1,
                        'keterangan'           => '-',
                    ],
                    [
                        'satuanKerja'          => 'Kejari Banda Aceh',
                        'kategoriTindakPidana' => 'Kamnegtibum dan TPUL',
                        'sisaBulanLalu'        => 19,
                        'masukBulanLaporan'    => 22,
                        'jumlahBulanLaporan'   => 41,
                        'sisaBulanLaporan'     => 36,
                        'keterangan'           => '-',
                    ],
                    [
                        'satuanKerja'          => 'Kejari Banda Aceh',
                        'kategoriTindakPidana' => 'Narkotika dan Zat Adiktif',
                        'sisaBulanLalu'        => 13,
                        'masukBulanLaporan'    => 1,
                        'jumlahBulanLaporan'   => 14,
                        'sisaBulanLaporan'     => 8,
                        'keterangan'           => '-',
                    ],
                    [
                        'satuanKerja'          => 'Kejari Banda Aceh',
                        'kategoriTindakPidana' => 'OHARDA',
                        'sisaBulanLalu'        => 53,
                        'masukBulanLaporan'    => 6,
                        'jumlahBulanLaporan'   => 59,
                        'sisaBulanLaporan'     => 55,
                        'keterangan'           => '-',
                    ],
                    [
                        'satuanKerja'          => 'Kejari Banda Aceh',
                        'kategoriTindakPidana' => 'Korupsi',
                        'sisaBulanLalu'        => 1,
                        'masukBulanLaporan'    => 0,
                        'jumlahBulanLaporan'   => 1,
                        'sisaBulanLaporan'     => 0,
                        'keterangan'           => '-',
                    ],
                ],
            ]
        );
    }
}