<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Default ke Bulan dan Tahun Sekarang jika tidak ada filter[cite: 5]
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        // Fetch Form Template 3A dan 3C berdasarkan Bulan & Tahun Pilihan[cite: 5]
        $forms3A = FormTemplate::where('form_type', '3A')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $forms3C = FormTemplate::where('form_type', '3C')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        // Counter Statistik Ringkasan[cite: 5]
        $totalPerkaraMasuk3A = 0;
        $totalPerkaraSelesai3C = 0;
        $narkotikaSummary = []; // Array dinamis menampung semua jenis narkotika

        // Peta Pemetaan Kategori Tindak Pidana untuk Tabel Matriks[cite: 5]
        $categoriesMap = [
            'Kamnegtibum dan TPUL' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'Narkotika dan Zat Adiktif' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'OHARDA' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'Teroris' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'Korupsi' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
            'Diantar' => ['masukCases' => 0, 'masukUnits' => 0, 'sisaBulanLalu' => 0, 'selesaiCases' => 0, 'selesaiUnits' => 0, 'dikembalikan' => [], 'dimusnahkan' => [], 'lelang' => []],
        ];

        // Peta Hitung Kategori Chart (Pie Chart)[cite: 5]
        $categoryCounts = [
            'NARKOTIKA DAN ZAT ADIKTIF' => 0,
            'OHARDA' => 0,
            'KAMNEGTIBUM DAN TPUL' => 0,
            'KORUPSI' => 0,
            'TERORIS' => 0,
        ];

        $recentCases = [];

        // 1. OLAH DATA FORM 3A (MASUK)[cite: 5]
        foreach ($forms3A as $form) {
            $allCases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

            foreach ($allCases as $caseIndex => $summary) {
                $totalPerkaraMasuk3A++;

                $katRaw = strtoupper($summary['kategoriTindakPidana'] ?? '');
                $katKey = $this->matchCategoryKey($katRaw);

                $bbList = $summary['barangBuktiList'] ?? [];
                $unitCount = count($bbList) > 0 ? count($bbList) : 1;

                if (isset($categoriesMap[$katKey])) {
                    $categoriesMap[$katKey]['masukCases']++;
                    $categoriesMap[$katKey]['masukUnits'] += $unitCount;
                }

                $chartKey = $this->matchChartCategoryKey($katRaw);
                if (isset($categoryCounts[$chartKey])) {
                    $categoryCounts[$chartKey]++;
                }

                // Akumulasi Kuantitas Narkotika secara Dinamis & Otomatis Konversi ke Gram
                foreach ($bbList as $bb) {
                    $rawJenis = trim($bb['jenisNarkotika'] ?? '');

                    if (!empty($rawJenis)) {
                        $jenisKey = strtoupper($rawJenis);
                        $jumlahVal = (float)($bb['jumlahNarkotika'] ?? $bb['jumlah'] ?? 0);
                        $satuanVal = strtolower(trim($bb['satuanNarkotika'] ?? $bb['satuan'] ?? ''));

                        // Jika Kilogram / Kg, konversi ke Gram. Selain itu gunakan nilai & satuan aslinya
                        if (in_array($satuanVal, ['kilogram (kg)', 'kg', 'kilogram'])) {
                            $valInGram = $jumlahVal * 1000;
                            $unitLabel = 'Gram';
                        } elseif (in_array($satuanVal, ['gram (g)', 'g', 'gram'])) {
                            $valInGram = $jumlahVal;
                            $unitLabel = 'Gram';
                        } else {
                            $valInGram = $jumlahVal;
                            $unitLabel = !empty($bb['satuanNarkotika']) ? $bb['satuanNarkotika'] : (!empty($bb['satuan']) ? $bb['satuan'] : 'Pcs');
                        }

                        if (!isset($narkotikaSummary[$jenisKey])) {
                            $narkotikaSummary[$jenisKey] = [
                                'nama'   => $jenisKey,
                                'jumlah' => 0,
                                'satuan' => $unitLabel,
                            ];
                        }
                        $narkotikaSummary[$jenisKey]['jumlah'] += $valInGram;
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

        // 2. OLAH DATA FORM 3C (SELESAI / PUTUS)[cite: 5]
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

                    // Counter item BB per-perkara[cite: 5]
                    $dikembalikanCount = 0;
                    $dimusnahkanCount = 0;
                    $lelangCount = 0;

                    // Hitung berapa item BB di perkara ini untuk masing-masing status[cite: 5]
                    foreach ($bbList as $bb) {
                        $amar = strtolower($bb['amarPutusan'] ?? '');

                        if (str_contains($amar, 'kembali')) {
                            $dikembalikanCount++;
                        } elseif (str_contains($amar, 'musnah')) {
                            $dimusnahkanCount++;
                        } elseif (str_contains($amar, 'lelang') || str_contains($amar, 'rampas')) {
                            $lelangCount++;
                        }
                    }

                    // Push hasil total item per-perkara ke array kategori jika ada (> 0)[cite: 5]
                    if ($dikembalikanCount > 0) {
                        $categoriesMap[$katKey]['dikembalikan'][] = $dikembalikanCount;
                    }
                    if ($dimusnahkanCount > 0) {
                        $categoriesMap[$katKey]['dimusnahkan'][] = $dimusnahkanCount;
                    }
                    if ($lelangCount > 0) {
                        $categoriesMap[$katKey]['lelang'][] = $lelangCount;
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

        // Format Ulang Array Matriks Rekapitulasi[cite: 5]
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
                'narkotikaSummary' => array_values($narkotikaSummary),
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