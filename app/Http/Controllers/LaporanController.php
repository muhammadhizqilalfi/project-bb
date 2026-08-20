<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use App\Services\LaporanDocxService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PDF;

class LaporanController extends Controller
{
    public function getLaporanData(Request $request)
    {
        $formType = strtoupper($request->input('formType', '3A'));
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $kategori = $request->input('kategori', 'ALL');

        $count3A = $this->countCasesByPeriod('3A', $month, $year, $kategori);
        $count3B = 1;
        $count3C = $this->countCasesByPeriod('3C', $month, $year, $kategori);

        $counts = [
            'form3a' => $count3A,
            'form3b' => $count3B,
            'form3c' => $count3C,
        ];

        $cases = [];
        $sabuGram = 0;
        $ganjaGram = 0;
        $ekstasiPcs = 0;

        if ($formType === '3B') {
            $sisaBulanLalu = $this->getSisaBulanLalu($month, $year, $kategori);
            $masukBulanLaporan = $count3A;
            $jumlahBulanLaporan = $sisaBulanLalu + $masukBulanLaporan;
            $perkaraSelesai = $count3C;
            $sisaBulanLaporan = max(0, $jumlahBulanLaporan - $perkaraSelesai);

            $cases = [
                [
                    'id'                  => 'form3b-summary',
                    'satuanKerja'         => 'Kejari Banda Aceh',
                    'sisaBulanLalu'       => $sisaBulanLalu,
                    'masukBulanLaporan'   => $masukBulanLaporan,
                    'jumlahBulanLaporan' => $jumlahBulanLaporan,
                    'perkaraSelesai'      => $perkaraSelesai,
                    'sisaBulanLaporan'    => $sisaBulanLaporan,
                    'keterangan'          => '-',
                ]
            ];
        } else {
            $formsQuery = FormTemplate::where('form_type', $formType)
                ->where('month', $month)
                ->where('year', $year)
                ->get();

            foreach ($formsQuery as $form) {
                $allCases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

                foreach ($allCases as $caseIndex => $summary) {
                    $kategoriCase = $summary['kategoriTindakPidana'] ?? '';

                    if ($kategori !== 'ALL' && strtoupper($kategoriCase) !== strtoupper($kategori)) {
                        continue;
                    }

                    $rawBbList = $summary['barangBuktiList'] ?? [];

                    $formattedBbList = array_map(function ($bb) {
                        return [
                            'jenisBarangBukti'  => $bb['jenisBarangBukti'] ?? $bb['uraianBarangBukti'] ?? '-',
                            'uraianBarangBukti' => $bb['uraianBarangBukti'] ?? $bb['jenisBarangBukti'] ?? '-',
                            'jumlah'            => (float) ($bb['jumlah'] ?? $bb['jumlahSatuan'] ?? 0),
                            'satuan'            => $bb['satuan'] ?? $bb['jenisSatuan'] ?? '-',
                            'tempatPenyimpanan' => $bb['tempatPenyimpanan'] ?? '-',
                            'jenisNarkotika'    => $bb['jenisNarkotika'] ?? null,
                            'jumlahNarkotika'   => isset($bb['jumlahNarkotika']) ? (float) $bb['jumlahNarkotika'] : null,
                            'satuanNarkotika'   => $bb['satuanNarkotika'] ?? null,
                            'macamJenisKadar'   => $bb['macamJenisKadar'] ?? null,
                            'amarPutusan'       => $bb['amarPutusan'] ?? null,
                            'uraianPutusan'     => $bb['uraianPutusan'] ?? null,
                        ];
                    }, $rawBbList);

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
                        'id'                    => (string) $form->id,
                        'case_index'            => $caseIndex,
                        'satuanKerja'           => $summary['satuanKerja'] ?? 'Kejari Banda Aceh',
                        'noRegBendaSitaan'     => $summary['noRegBendaSitaan'] ?? '-',
                        'noRegPenyidikan'      => $summary['noRegPenyidikan'] ?? '-',
                        'identitasTersangka'    => $summary['identitasTersangka'] ?? '-',
                        'pasalDisangkakan'      => $summary['pasalDisangkakan'] ?? $summary['pasalDidakwakan'] ?? '-',
                        'pasalDidakwakan'        => $summary['pasalDidakwakan'] ?? $summary['pasalDisangkakan'] ?? '-',
                        'statusDiselesaikan'    => $summary['statusDiselesaikan'] ?? '-',
                        'tglPelaksanaanPutusan' => $summary['tglPelaksanaanPutusan'] ?? '-',
                        'keterangan'            => $summary['keterangan'] ?? '-',
                        'barangBuktiList'       => $formattedBbList,
                        'tglPenerimaan'         => $summary['tglPenerimaan'] ?? '-',
                        'tglRegPenyidikan'      => $summary['tglRegPenyidikan'] ?? '-',
                        'noKepPengadilan'       => $summary['noKepPengadilan'] ?? '-',
                        'tglKepPengadilan'      => $summary['tglKepPengadilan'] ?? '-',
                        'amarPutusan'           => $summary['amarPutusan'] ?? '-',
                    ];
                }
            }
        }

        return [
            'filters' => [
                'formType' => $formType,
                'month'    => $month,
                'year'     => $year,
                'kategori' => $kategori,
            ],
            'counts' => $counts,
            'summaryNarkotika' => [
                'sabuGram'   => $sabuGram,
                'ganjaGram'  => $ganjaGram,
                'ekstasiPcs' => $ekstasiPcs,
            ],
            'cases' => $cases,
        ];
    }

    private function countCasesByPeriod(string $formType, int $month, int $year, string $kategori): int
    {
        $forms = FormTemplate::where('form_type', $formType)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $count = 0;
        foreach ($forms as $form) {
            $allCases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);
            foreach ($allCases as $summary) {
                $kategoriCase = $summary['kategoriTindakPidana'] ?? '';
                if ($kategori !== 'ALL' && strtoupper($kategoriCase) !== strtoupper($kategori)) {
                    continue;
                }
                $count++;
            }
        }

        return $count;
    }

    private function getSisaBulanLalu(int $targetMonth, int $targetYear, string $kategori): int
    {
        $earliestForm = FormTemplate::orderBy('year', 'asc')->orderBy('month', 'asc')->first();
        if (!$earliestForm) {
            return 0;
        }

        $curY = (int) $earliestForm->year;
        $curM = (int) $earliestForm->month;

        $runningSisaBulanLaporan = 0;

        while ($curY < $targetYear || ($curY === $targetYear && $curM < $targetMonth)) {
            $masuk = $this->countCasesByPeriod('3A', $curM, $curY, $kategori);
            $selesai = $this->countCasesByPeriod('3C', $curM, $curY, $kategori);

            $jumlah = $runningSisaBulanLaporan + $masuk;
            $runningSisaBulanLaporan = max(0, $jumlah - $selesai);

            $curM++;
            if ($curM > 12) {
                $curM = 1;
                $curY++;
            }
        }

        return $runningSisaBulanLaporan;
    }

    public function Laporan(Request $request)
    {
        $data = $this->getLaporanData($request);
        return Inertia::render('Tabs/Laporan', $data);
    }

    public function exportDocx(Request $request, LaporanDocxService $docxService)
    {
        if ($request->input('kategori', 'ALL') === 'ALL') {
            return back()->with('error', 'Silakan pilih kategori Tindak Pidana spesifik terlebih dahulu.');
        }

        $data = $this->getLaporanData($request);

        $paper = [0, 0, 13 * 72, 8.5 * 72];

        $pdf = PDF::loadView('exports-laporan-pdf', $data, [], [
            'mode'                 => 'utf-8',
            'format'               => 'A4-L',
            'margin_left'          => 12,
            'margin_right'         => 12,
            'margin_top'           => 12,
            'margin_bottom'        => 12,
            'default_font'         => 'arial',
            'shrink_tables_to_fit' => 1,
        ]);

        $fileName = "Laporan_Form_{$data['filters']['formType']}_{$data['filters']['month']}_{$data['filters']['year']}.pdf";

        return $pdf->stream($fileName);
    }

    public function exportDocx(Request $request, LaporanDocxService $docxService)
    {
        if ($request->input('kategori', 'ALL') === 'ALL') {
            return back()->with(
                'error',
                'Silakan pilih kategori Tindak Pidana spesifik terlebih dahulu.'
            );
        }

        try {
            $data = $this->getLaporanData($request);
            $fileName = "Laporan_Form_{$data['filters']['formType']}_{$data['filters']['month']}_{$data['filters']['year']}.docx";

            return $docxService->download($data, $fileName);
        } catch (\Throwable $e) {
            \Log::error('Gagal export DOCX laporan', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportPdf(Request $request, LaporanDocxService $docxService)
    {
        $kategori = $request->input('kategori', 'ALL');
        if ($kategori === 'ALL') {
            return back()->with('error', 'Silakan pilih kategori spesifik terlebih dahulu.');
        }

        try {
            $data = $this->getLaporanData($request);

            // 1. Generate dokumen Word (.docx) sementara
            $phpWord = $docxService->build($data);
            $tempId = 'pdf_conv_' . uniqid();
            
            $storageAppPath = storage_path('app');
            $tempDocxPath = str_replace('/', DIRECTORY_SEPARATOR, "{$storageAppPath}\\{$tempId}.docx");
            $outputDir = str_replace('/', DIRECTORY_SEPARATOR, $storageAppPath);

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($tempDocxPath);

            if (!file_exists($tempDocxPath)) {
                return back()->with('error', 'Gagal membuat file dokumen Word sementara.');
            }

            // 2. Eksekusi menggunakan Symfony Process (Jauh lebih stabil di Windows)
            $libreOfficePath = 'soffice'; // Default untuk Linux/Docker
            $workingDir = null;

            if (PHP_OS_FAMILY === 'Windows') {
                $workingDir = 'C:\Program Files\LibreOffice\program';
                if (!is_dir($workingDir) && is_dir('C:\Program Files (x86)\LibreOffice\program')) {
                    $workingDir = 'C:\Program Files (x86)\LibreOffice\program';
                }
                
                // Gunakan soffice.exe secara spesifik
                $libreOfficePath = $workingDir . '\soffice.exe'; 
            }

            // Susun argumen sebagai array (Process otomatis mengurus escaping path)
            $process = new \Symfony\Component\Process\Process([
                $libreOfficePath,
                '--headless',
                '--convert-to',
                'pdf',
                $tempDocxPath,
                '--outdir',
                $outputDir
            ]);

            // Mengunci Working Directory langsung ke markas LibreOffice
            if ($workingDir) {
                $process->setWorkingDirectory($workingDir);
            }

            // Beri waktu proses yang cukup
            $process->setTimeout(120);
            
            // Jalankan proses
            $process->run();

            $tempPdfPath = str_replace('/', DIRECTORY_SEPARATOR, "{$storageAppPath}\\{$tempId}.pdf");

            // 3. Pembersihan (Cleanup) file DOCX
            if (file_exists($tempDocxPath)) {
                @unlink($tempDocxPath);
            }

            // 4. Validasi hasil (Menangkap error jauh lebih detail)
            if (!$process->isSuccessful() || !file_exists($tempPdfPath)) {
                dd([
                    'Pesan Error'    => 'File PDF gagal dibuat oleh LibreOffice.',
                    'Exit Code'      => $process->getExitCode(),
                    'Error Output'   => $process->getErrorOutput(),
                    'Standard Output'=> $process->getOutput(),
                    'Command Line'   => $process->getCommandLine(),
                ]);
            }

            $fileName = "Laporan_Form_{$data['filters']['formType']}_{$data['filters']['month']}_{$data['filters']['year']}.pdf";

            return response()->download($tempPdfPath, $fileName, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            ])->deleteFileAfterSend(true);

        } catch (\Throwable $e) {
            dd([
                'Exception Message' => $e->getMessage(),
                'File'              => $e->getFile(),
                'Line'              => $e->getLine(),
            ]);
        }
    }
}