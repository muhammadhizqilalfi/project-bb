<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Default ke Bulan dan Tahun Sekarang jika tidak ada filter
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        // Fetch Form Template 3A dan 3C berdasarkan Bulan & Tahun Pilihan
        $forms3A = FormTemplate::where('form_type', '3A')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $forms3C = FormTemplate::where('form_type', '3C')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        // Counter Statistik Ringkasan
        $totalPerkaraMasuk3A = 0;
        $totalPerkaraSelesai3C = 0;
        $sabuGram = 0;
        $ganjaGram = 0;
        $ekstasiPcs = 0;

        // Peta Pemetaan Kategori Tindak Pidana untuk Tabel Matriks
        $categoriesMap = [
            'Kamnegtibum dan TPUL' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'Narkotika dan Zat Adiktif' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'OHARDA' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'Teroris' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'Korupsi' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'Diantar' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
        ];

        // Peta Hitung Kategori Chart (Pie Chart)
        $categoryCounts = [
            'NARKOTIKA DAN ZAT ADIKTIF' => 0,
            'OHARDA' => 0,
            'KAMNEGTIBUM DAN TPUL' => 0,
            'KORUPSI' => 0,
            'TERORIS' => 0,
        ];

        $recentCases = [];

        // 1. OLAH DATA FORM 3A (MASUK)
        foreach ($forms3A as $form) {
            $allCases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

            foreach ($allCases as $caseIndex => $summary) {
                $totalPerkaraMasuk3A++;

                $katRaw = strtoupper($summary['kategoriTindakPidana'] ?? '');
                $katKey = $this->matchCategoryKey($katRaw);

                $bbList = $summary['barangBuktiList'] ?? [];
                $unitCount = count($bbList) > 0 ? array_sum(array_map(fn($b) => (float)($b['jumlah'] ?? $b['jumlahSatuan'] ?? 1), $bbList)) : 1;

                if (isset($categoriesMap[$katKey])) {
                    $categoriesMap[$katKey]['masukCases']++;
                    $categoriesMap[$katKey]['masukUnits'] += $unitCount;
                }

                $chartKey = $this->matchChartCategoryKey($katRaw);
                if (isset($categoryCounts[$chartKey])) {
                    $categoryCounts[$chartKey]++;
                }

                // Akumulasi Massa Narkotika
                foreach ($bbList as $bb) {
                    $jenisNarkotika = strtolower($bb['jenisNarkotika'] ?? $bb['uraianBarangBukti'] ?? '');
                    $jumlahVal = (float)($bb['jumlahNarkotika'] ?? $bb['jumlah'] ?? 0);
                    $satuanVal = strtolower($bb['satuanNarkotika'] ?? $bb['satuan'] ?? '');

                    $massaGram = (in_array($satuanVal, ['kilogram (kg)', 'kg', 'kilogram'])) ? $jumlahVal * 1000 : $jumlahVal;

                    if (str_contains($jenisNarkotika, 'sabu') || str_contains($jenisNarkotika, 'meth')) {
                        $sabuGram += $massaGram;
                    } elseif (str_contains($jenisNarkotika, 'ganja') || str_contains($jenisNarkotika, 'cannabis')) {
                        $ganjaGram += $massaGram;
                    } elseif (str_contains($jenisNarkotika, 'ekstasi') || str_contains($jenisNarkotika, 'inex') || str_contains($jenisNarkotika, 'pil')) {
                        $ekstasiPcs += $jumlahVal;
                    }
                }

                $recentCases[] = [
                    'id' => (string) $form->id,
                    'caseIndex' => $caseIndex,
                    'noRegSitaan' => $summary['noRegBendaSitaan'] ?? '-',
                    'tersangka' => $summary['identitasTersangka'] ?? '-',
                    'kategori' => $summary['kategoriTindakPidana'] ?? '-',
                    'tempatPenyimpanan' => $bbList[0]['tempatPenyimpanan'] ?? '-',
                    'formType' => '3A',
                    'createdAt' => $form->created_at,
                ];
            }
        }

        // 2. OLAH DATA FORM 3C (SELESAI / PUTUS)
        foreach ($forms3C as $form) {
            $allCases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

            foreach ($allCases as $caseIndex => $summary) {
                $totalPerkaraSelesai3C++;

                $katRaw = strtoupper($summary['kategoriTindakPidana'] ?? '');
                $katKey = $this->matchCategoryKey($katRaw);

                $bbList = $summary['barangBuktiList'] ?? [];
                $unitCount = count($bbList) > 0 ? count($bbList) : 1;

                if (isset($categoriesMap[$katKey])) {
                    $categoriesMap[$katKey]['selesaiCases']++;
                    $categoriesMap[$katKey]['selesaiUnits'] += $unitCount;

                    // Rincian Amar Putusan
                    foreach ($bbList as $bb) {
                        $amar = strtolower($bb['amarPutusan'] ?? '');
                        $qty = (int)($bb['jumlahSatuan'] ?? $bb['jumlah'] ?? 1);

                        if (str_contains($amar, 'kembali')) {
                            $categoriesMap[$katKey]['dikembalikan'][] = $qty;
                        } elseif (str_contains($amar, 'musnah')) {
                            $categoriesMap[$katKey]['dimusnahkan'][] = $qty;
                        } elseif (str_contains($amar, 'lelang') || str_contains($amar, 'rampas')) {
                            $categoriesMap[$katKey]['lelang'][] = $qty;
                        }
                    }
                }

                $chartKey = $this->matchChartCategoryKey($katRaw);
                if (isset($categoryCounts[$chartKey])) {
                    $categoryCounts[$chartKey]++;
                }

                $recentCases[] = [
                    'id' => (string) $form->id,
                    'caseIndex' => $caseIndex,
                    'noRegSitaan' => $summary['noRegBendaSitaan'] ?? '-',
                    'tersangka' => $summary['identitasTersangka'] ?? $summary['pasalDidakwakan'] ?? '-',
                    'kategori' => $summary['kategoriTindakPidana'] ?? '-',
                    'tempatPenyimpanan' => $bbList[0]['tempatPenyimpanan'] ?? '-',
                    'formType' => '3C',
                    'createdAt' => $form->created_at,
                ];
            }
        }

        // Format Ulang Array Matriks Rekapitulasi
        $rekapitulasiMatriks = [];
        foreach ($categoriesMap as $kategoriLabel => $val) {
            $rekapitulasiMatriks[] = [
                'kategori' => $kategoriLabel,
                'masuk' => "{$val['masukCases']} / {$val['masukUnits']}",
                'sisaBulanLalu' => $val['sisaBulanLalu'],
                'selesai' => "{$val['selesaiCases']} / {$val['selesaiUnits']}",
                'dikembalikan' => !empty($val['dikembalikan']) ? implode(', ', $val['dikembalikan']) : '-',
                'dimusnahkan' => !empty($val['dimusnahkan']) ? implode(', ', $val['dimusnahkan']) : '-',
                'lelang' => !empty($val['lelang']) ? implode(', ', $val['lelang']) : '-',
            ];
        }

        usort($recentCases, fn($a, $b) => strcmp($b['createdAt'], $a['createdAt']));
        $recentCases = array_slice($recentCases, 0, 5);

        $monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        return Inertia::render('Dashboard/Dashboard', [
            'filters' => [
                'month' => $month,
                'year' => $year,
            ],
            'stats' => [
                'totalPerkaraMasuk3A' => $totalPerkaraMasuk3A,
                'totalPerkaraSelesai3C' => $totalPerkaraSelesai3C,
                'sabuGram' => $sabuGram,
                'ganjaGram' => $ganjaGram,
                'ekstasiPcs' => $ekstasiPcs,
            ],
            'categoryChartData' => [
                'labels' => array_keys($categoryCounts),
                'data' => array_values($categoryCounts),
            ],
            'recentCases' => $recentCases,
            'rekapitulasiMatriks' => $rekapitulasiMatriks,
            'currentMonthName' => $monthNames[$month] ?? 'Agustus',
            'currentYear' => $year,
        ]);
    }

    private function matchCategoryKey(string $raw): string
    {
        if (str_contains($raw, 'KAMNEGTIBUM')) return 'Kamnegtibum dan TPUL';
        if (str_contains($raw, 'NARKOTIKA')) return 'Narkotika dan Zat Adiktif';
        if (str_contains($raw, 'OHARDA')) return 'OHARDA';
        if (str_contains($raw, 'TERORIS')) return 'Teroris';
        if (str_contains($raw, 'KORUPSI')) return 'Korupsi';
        if (str_contains($raw, 'DIANTAR')) return 'Diantar';
        return 'Kamnegtibum dan TPUL';
    }

    private function matchChartCategoryKey(string $raw): string
    {
        if (str_contains($raw, 'NARKOTIKA')) return 'NARKOTIKA DAN ZAT ADIKTIF';
        if (str_contains($raw, 'OHARDA')) return 'OHARDA';
        if (str_contains($raw, 'KAMNEGTIBUM')) return 'KAMNEGTIBUM DAN TPUL';
        if (str_contains($raw, 'KORUPSI')) return 'KORUPSI';
        if (str_contains($raw, 'TERORIS')) return 'TERORIS';
        return 'KAMNEGTIBUM DAN TPUL';
    }
}