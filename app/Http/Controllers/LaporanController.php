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

        // Fetch Form Template berdasarkan Tipe, Bulan, dan Tahun
        $formsQuery = FormTemplate::where('form_type', $formType)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        // Hitung total count per form untuk angka pada Tab Badge
        $counts = [
            'form3a' => FormTemplate::where('form_type', '3A')->where('month', $month)->where('year', $year)->count(),
            'form3b' => FormTemplate::where('form_type', '3B')->where('month', $month)->where('year', $year)->count(),
            'form3c' => FormTemplate::where('form_type', '3C')->where('month', $month)->where('year', $year)->count(),
        ];

        // Format data list perkara untuk preview tabel
        $cases = [];
        
        // Akumulator Massa Narkotika
        $sabuGram = 0;
        $ganjaGram = 0;
        $ekstasiPcs = 0;

        foreach ($formsQuery as $form) {
            $summary = $form->latest_case_summary ?? [];
            $bbList = $summary['barangBuktiList'] ?? [];

            $totalBeratCaseGram = 0;
            $kategoriDominan = $summary['kategoriTindakPidana'] ?? 'NARKOTIKA GOL I';

            foreach ($bbList as $bb) {
                $jenis = strtolower($bb['jenisBarangBukti'] ?? $bb['namaBarangBukti'] ?? '');
                $jumlah = (float) ($bb['jumlah'] ?? $bb['jumlahSatuan'] ?? 0);
                $satuan = strtolower($bb['satuan'] ?? $bb['jenisSatuan'] ?? '');

                // Konversi jumlah ke gram jika satuan Kg
                $massaGram = $satuan === 'kilogram (kg)' || $satuan === 'kg' ? $jumlah * 1000 : $jumlah;
                $totalBeratCaseGram += $massaGram;

                // KALKULASI TOTAL OTOMATIS CARD NARKOTIKA
                if (str_contains($jenis, 'sabu') || str_contains($jenis, 'meth')) {
                    $sabuGram += $massaGram;
                } elseif (str_contains($jenis, 'ganja') || str_contains($jenis, 'cannabis')) {
                    $ganjaGram += $massaGram;
                } elseif (str_contains($jenis, 'ekstasi') || str_contains($jenis, 'inex') || str_contains($jenis, 'pil')) {
                    $ekstasiPcs += $jumlah;
                }
            }

            $cases[] = [
                'id' => (string) $form->id,
                'noReg' => $summary['noRegBendaSitaan'] ?? $summary['noRegPenyidikan'] ?? $form->name,
                'namaTersangka' => $summary['identitasTersangka'] ?? '-',
                'kategoriBarang' => strtoupper($kategoriDominan),
                'beratGram' => $totalBeratCaseGram,
                'statusKontrol' => !empty($summary['tglPelaksanaanPutusan']) ? 'Selesai (Siap Ekspor)' : 'Berjalan (Editable)',
                'barangBuktiList' => $bbList,
            ];
        }

        return Inertia::render('Tabs/Laporan', [
            'filters' => [
                'formType' => $formType,
                'month' => $month,
                'year' => $year,
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
        $ids = explode(',', $request->input('ids', ''));

        $forms = FormTemplate::whereIn('id', $ids)->get();

        // Mengembalikan view khusus cetak / PDF generator (misal DomPDF/Snappy)
        return view('pdf.laporan-template', [
            'formType' => $formType,
            'forms' => $forms,
        ]);
    }
}