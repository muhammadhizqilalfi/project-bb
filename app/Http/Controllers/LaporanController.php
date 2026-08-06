<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LaporanController extends Controller
{
    public function Laporan(Request $request)
    {
        $formType = strtoupper($request->input('formType', '3A'));
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $kategori = $request->input('kategori', 'ALL');

        // Fetch Form Template berdasarkan Tipe, Bulan, dan Tahun
        $formsQuery = FormTemplate::where('form_type', $formType)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        // Hitung total akumulasi SELURUH case di setiap form untuk badge tab
        $count3A = 0;
        foreach (FormTemplate::where('form_type', '3A')->where('month', $month)->where('year', $year)->get() as $f) {
            $count3A += count($f->cases ?? ($f->latest_case_summary ? [$f->latest_case_summary] : []));
        }

        $count3B = 0;
        foreach (FormTemplate::where('form_type', '3B')->where('month', $month)->where('year', $year)->get() as $f) {
            $count3B += count($f->cases ?? ($f->latest_case_summary ? [$f->latest_case_summary] : []));
        }

        $count3C = 0;
        foreach (FormTemplate::where('form_type', '3C')->where('month', $month)->where('year', $year)->get() as $f) {
            $count3C += count($f->cases ?? ($f->latest_case_summary ? [$f->latest_case_summary] : []));
        }

        $counts = [
            'form3a' => $count3A,
            'form3b' => $count3B,
            'form3c' => $count3C,
        ];

        $cases = [];
        
        // Akumulator Massa Narkotika
        $sabuGram = 0;
        $ganjaGram = 0;
        $ekstasiPcs = 0;

        foreach ($formsQuery as $form) {
            // PERBAIKAN: Ambil SELURUH array cases, bukan hanya latest_case_summary
            $allCases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

            foreach ($allCases as $caseIndex => $summary) {
                $kategoriCase = $summary['kategoriTindakPidana'] ?? '';

                // Filter berdasarkan Kategori Tindak Pidana jika tidak memilih 'ALL'
                if ($kategori !== 'ALL' && strtoupper($kategoriCase) !== strtoupper($kategori)) {
                    continue;
                }

                $rawBbList = $summary['barangBuktiList'] ?? [];

                // Memastikan daftar barang bukti memuat jumlah unit asli yang diisi saat input
                $formattedBbList = array_map(function ($bb) {
                    return [
                        'jenisBarangBukti' => $bb['jenisBarangBukti'] ?? $bb['uraianBarangBukti'] ?? '-',
                        'uraianBarangBukti' => $bb['uraianBarangBukti'] ?? $bb['jenisBarangBukti'] ?? '-',
                        'jumlah' => (float) ($bb['jumlah'] ?? $bb['jumlahSatuan'] ?? 0),
                        'satuan' => $bb['satuan'] ?? $bb['jenisSatuan'] ?? '-',
                        'tempatPenyimpanan' => $bb['tempatPenyimpanan'] ?? '-',
                        'jenisNarkotika' => $bb['jenisNarkotika'] ?? null,
                        'jumlahNarkotika' => isset($bb['jumlahNarkotika']) ? (float) $bb['jumlahNarkotika'] : null,
                        'satuanNarkotika' => $bb['satuanNarkotika'] ?? null,
                        'macamJenisKadar' => $bb['macamJenisKadar'] ?? null,
                        'amarPutusan' => $bb['amarPutusan'] ?? null,
                        'uraianPutusan' => $bb['uraianPutusan'] ?? null,
                    ];
                }, $rawBbList);

                // Hitung akumulasi ringkasan narkotika berdasarkan nilai jumlah & satuan hasil input
                foreach ($formattedBbList as $bb) {
                    $jenisNarkotika = strtolower($bb['jenisNarkotika'] ?? $bb['uraianBarangBukti'] ?? '');
                    
                    $jumlahVal = $bb['jumlahNarkotika'] !== null ? $bb['jumlahNarkotika'] : $bb['jumlah'];
                    $satuanVal = strtolower($bb['satuanNarkotika'] ?? $bb['satuan'] ?? '');

                    $massaGram = ($satuanVal === 'kilogram (kg)' || $satuanVal === 'kg') 
                        ? $jumlahVal * 1000 
                        : $jumlahVal;

                    if (str_contains($jenisNarkotika, 'sabu') || str_contains($jenisNarkotika, 'meth')) {
                        $sabuGram += $massaGram;
                    } elseif (str_contains($jenisNarkotika, 'ganja') || str_contains($jenisNarkotika, 'cannabis')) {
                        $ganjaGram += $massaGram;
                    } elseif (str_contains($jenisNarkotika, 'ekstasi') || str_contains($jenisNarkotika, 'inex') || str_contains($jenisNarkotika, 'pil')) {
                        $ekstasiPcs += $jumlahVal;
                    }
                }

                $cases[] = [
                    'id' => (string) $form->id,
                    'case_index' => $caseIndex,
                    'satuanKerja' => $summary['satuanKerja'] ?? '-',
                    'noRegBendaSitaan' => $summary['noRegBendaSitaan'] ?? '-',
                    'noRegPenyidikan' => $summary['noRegPenyidikan'] ?? '-',
                    'identitasTersangka' => $summary['identitasTersangka'] ?? '-',
                    'pasalDisangkakan' => $summary['pasalDisangkakan'] ?? $summary['pasalDidakwakan'] ?? '-',
                    'pasalDidakwakan' => $summary['pasalDidakwakan'] ?? $summary['pasalDisangkakan'] ?? '-',
                    'statusDiselesaikan' => $summary['statusDiselesaikan'] ?? '-',
                    'tglPelaksanaanPutusan' => $summary['tglPelaksanaanPutusan'] ?? '-',
                    'keterangan' => $summary['keterangan'] ?? '-',
                    'barangBuktiList' => $formattedBbList,

                    'sisaBulanLalu' => $summary['sisaBulanLalu'] ?? 0,
                    'masukBulanLaporan' => $summary['masukBulanLaporan'] ?? 0,
                    'jumlahBulanLaporan' => $summary['jumlahBulanLaporan'] ?? 0,
                    'sisaBulanLaporan' => $summary['sisaBulanLaporan'] ?? 0,
                    'tglPenerimaan' => $summary['tglPenerimaan'] ?? '-',
                    'tglRegPenyidikan' => $summary['tglRegPenyidikan'] ?? '-',
                    'noKepPengadilan' => $summary['noKepPengadilan'] ?? '-',
                    'tglKepPengadilan' => $summary['tglKepPengadilan'] ?? '-',
                    'amarPutusan' => $summary['amarPutusan'] ?? '-',
                ];
            }
        }

        return Inertia::render('Tabs/Laporan', [
            'filters' => [
                'formType' => $formType,
                'month' => $month,
                'year' => $year,
                'kategori' => $kategori,
            ],
            'counts' => $counts,
            'summaryNarkotika' => [
                'sabuGram' => $sabuGram,
                'ganjaGram' => $ganjaGram,
                'ekstasiPcs' => $ekstasiPcs,
            ],
            'cases' => $cases,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $formType = $request->input('formType', '3A');
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $forms = FormTemplate::where('form_type', $formType)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        return view('pdf.laporan-template', [
            'formType' => $formType,
            'forms' => $forms,
        ]);
    }
}