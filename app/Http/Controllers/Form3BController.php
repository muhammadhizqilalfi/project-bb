<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Form3BController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $kategori = $request->input('kategori', 'KAMNEGTIBUM DAN TPUL');

        // 1. HITUNG SISA BULAN LALU (Data Akumulasi Sebelum Bulan/Tahun Pilihan)
        $sisaBulanLalu = $this->calculateSisaBulanLalu($month, $year, $kategori);

        // 2. HITUNG MASUK BULAN LAPORAN (Dihitung dari Form 3A Bulan Ini)
        $masukBulanLaporan = $this->countCasesFromForm('3A', $month, $year, $kategori);

        // 3. JUMLAH BULAN LAPORAN (Sisa Lalu + Masuk Bulan Ini)
        $jumlahBulanLaporan = $sisaBulanLalu + $masukBulanLaporan;

        // 4. HITUNG PERKARA SELESAI (Dihitung dari Form 3C Bulan Ini)
        $perkaraSelesai = $this->countCasesFromForm('3C', $month, $year, $kategori);

        // 5. SISA BULAN LAPORAN (Jumlah Bulan Ini - Perkara Selesai)
        $sisaBulanLaporan = max(0, $jumlahBulanLaporan - $perkaraSelesai);

        $monthNames = [
            1 => 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
            'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
        ];

        return Inertia::render('Tabs/Form3B', [
            'filters' => [
                'month' => $month,
                'year' => $year,
                'kategori' => $kategori,
                'selectedPeriod' => ($monthNames[$month] ?? 'AGUSTUS') . ' ' . $year,
            ],
            'calculatedData' => [
                'kejaksaan' => 'Kejari Banda Aceh',
                'sisaBulanLalu' => $sisaBulanLalu,
                'masukBulanLaporan' => $masukBulanLaporan,
                'jumlahBulanLaporan' => $jumlahBulanLaporan,
                'perkaraSelesai' => $perkaraSelesai,
                'sisaBulanLaporan' => $sisaBulanLaporan,
            ]
        ]);
    }

    /**
     * Hitung sisa perkara dari bulan-bulan sebelumnya
     */
    private function calculateSisaBulanLalu(int $month, int $year, string $kategori): int
    {
        // Hitung total Form 3A sebelum bulan/tahun aktif
        $total3ABefore = 0;
        $forms3A = FormTemplate::where('form_type', '3A')
            ->where(function ($q) use ($year, $month) {
                $q->where('year', '<', $year)
                  ->orWhere(function ($q2) use ($year, $month) {
                      $q2->where('year', $year)->where('month', '<', $month);
                  });
            })->get();

        foreach ($forms3A as $form) {
            $cases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);
            foreach ($cases as $c) {
                if ($this->matchCategory($c['kategoriTindakPidana'] ?? '', $kategori)) {
                    $total3ABefore++;
                }
            }
        }

        // Hitung total Form 3C sebelum bulan/tahun aktif
        $total3CBefore = 0;
        $forms3C = FormTemplate::where('form_type', '3C')
            ->where(function ($q) use ($year, $month) {
                $q->where('year', '<', $year)
                  ->orWhere(function ($q2) use ($year, $month) {
                      $q2->where('year', $year)->where('month', '<', $month);
                  });
            })->get();

        foreach ($forms3C as $form) {
            $cases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);
            foreach ($cases as $c) {
                if ($this->matchCategory($c['kategoriTindakPidana'] ?? '', $kategori)) {
                    $total3CBefore++;
                }
            }
        }

        return max(0, $total3ABefore - $total3CBefore);
    }

    /**
     * Hitung jumlah perkara di Form tertentu pada bulan & tahun aktif
     */
    private function countCasesFromForm(string $formType, int $month, int $year, string $kategori): int
    {
        $forms = FormTemplate::where('form_type', $formType)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $count = 0;
        foreach ($forms as $form) {
            $cases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);
            foreach ($cases as $c) {
                if ($this->matchCategory($c['kategoriTindakPidana'] ?? '', $kategori)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    private function matchCategory(string $raw, string $target): bool
    {
        $cleanRaw = strtoupper(trim($raw));
        $cleanTarget = strtoupper(trim($target));

        if (str_contains($cleanRaw, 'NARKOTIKA') && str_contains($cleanTarget, 'NARKOTIKA')) return true;
        if (str_contains($cleanRaw, 'KAMNEGTIBUM') && str_contains($cleanTarget, 'KAMNEGTIBUM')) return true;
        if (str_contains($cleanRaw, 'OHARDA') && str_contains($cleanTarget, 'OHARDA')) return true;
        if (str_contains($cleanRaw, 'TERORIS') && str_contains($cleanTarget, 'TERORIS')) return true;
        if (str_contains($cleanRaw, 'KORUPSI') && str_contains($cleanTarget, 'KORUPSI')) return true;

        return $cleanRaw === $cleanTarget;
    }
}