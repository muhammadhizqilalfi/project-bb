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
        
        // Hitung total data 3B dari database jika ada, jika tidak default 1
        $forms3BQuery = FormTemplate::where('form_type', '3B')->where('month', $month)->where('year', $year)->get();
        $count3B = $forms3BQuery->isNotEmpty() ? $forms3BQuery->count() : 1;
        
        $count3C = $this->countCasesByPeriod('3C', $month, $year, $kategori);

        // Hitung total data Form 3D, 3E, 3F
        $count3D = FormTemplate::where('form_type', '3D')->count();
        $count3E = FormTemplate::where('form_type', '3E')->count();
        $count3F = FormTemplate::where('form_type', '3F')->count();

        $counts = [
            'form3a' => $count3A,
            'form3b' => $count3B,
            'form3c' => $count3C,
            'form3d' => $count3D,
            'form3e' => $count3E,
            'form3f' => $count3F,
        ];

        $cases = [];
        $sabuGram = 0;
        $ganjaGram = 0;
        $ekstasiPcs = 0;

        if (in_array($formType, ['3D', '3E', '3F'])) {
            $formsQuery = FormTemplate::where('form_type', $formType)->latest()->get();

            foreach ($formsQuery as $form) {
                $casesList = $form->cases ?? [];
                if (is_string($casesList)) {
                    $casesList = json_decode($casesList, true) ?? [];
                }

                foreach ($casesList as $index => $c) {
                    $satuanKerja = $c['satuanKerja'] ?? $form->kejari ?? $c['kejari'] ?? 'Kejari Banda Aceh';

                    if ($formType === '3D') {
                        $rawItems = $c['items'] ?? [];
                        $formattedItems = array_map(function ($it) {
                            return [
                                'nama_barang'            => $it['nama_barang'] ?? '-',
                                'harga_taksiran'         => isset($it['harga_taksiran']) ? (float) $it['harga_taksiran'] : 0,
                                'instansi_penilai'       => $it['instansi_penilai'] ?? '-',
                                'tgl_penilaian'          => $it['tgl_penilaian'] ?? '-',
                                'nilai_laku'             => isset($it['nilai_laku']) ? (float) $it['nilai_laku'] : 0,
                                'tgl_pelaksanaan_lelang' => $it['tgl_pelaksanaan_lelang'] ?? $it['tgl_lelang'] ?? '-',
                                'status_lelang'          => $it['status_lelang'] ?? '-',
                                'keterangan'             => $it['keterangan'] ?? '-',
                            ];
                        }, $rawItems);

                        $cases[] = [
                            'id'             => (string) $form->id,
                            'case_index'     => $index,
                            'satuanKerja'    => $satuanKerja,
                            'terpidana_nama' => $c['terpidana_nama'] ?? '-',
                            'tgl_penyerahan' => $c['tgl_penyerahan'] ?? '-',
                            'putusan_no'     => $c['putusan_no'] ?? '-',
                            'putusan_tgl'    => $c['putusan_tgl'] ?? '-',
                            'perkara'        => $c['perkara'] ?? '-',
                            'items'          => $formattedItems,
                        ];
                    } else {
                        // Form 3E & 3F
                        $cases[] = [
                            'id'             => (string) $form->id,
                            'case_index'     => $index,
                            'satuanKerja'    => $satuanKerja,
                            'terpidana_nama' => $c['terpidana_nama'] ?? '-',
                            'putusan_no'     => $c['putusan_no'] ?? '-',
                            'putusan_tgl'    => $c['putusan_tgl'] ?? '-',
                            'items'          => $c['items'] ?? [
                                [
                                    'nama_barang' => $c['rincian_barang'] ?? '-',
                                    'jumlah'      => '',
                                    'satuan'      => '',
                                    'harga_jual'  => $c['harga_jual'] ?? 0
                                ]
                            ],
                            'harga_jual'     => (float) ($c['harga_jual'] ?? 0),
                            'tgl_penjualan'  => $c['tgl_penjualan'] ?? '-',
                            'ntpn'           => $c['ntpn'] ?? '-',
                            'keterangan'     => $c['keterangan'] ?? '-',
                        ];
                    }
                }
            }
        } elseif ($formType === '3B') {
            // 1. Cek apakah ada record Form 3B tersimpan di DB untuk bulan & tahun pilihan
            if ($forms3BQuery->isNotEmpty()) {
                foreach ($forms3BQuery as $form) {
                    $allCases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

                    if (is_string($allCases)) {
                        $allCases = json_decode($allCases, true);
                    }

                    if (is_array($allCases)) {
                        foreach ($allCases as $caseIndex => $summary) {
                            $kategoriCase = $summary['kategoriTindakPidana'] ?? '';

                            // Menggunakan pencocokan kategori fleksibel
                            if ($kategori !== 'ALL' && !$this->matchCategory($kategoriCase, $kategori)) {
                                continue;
                            }

                            $jumlah = (int) ($summary['jumlahBulanLaporan'] ?? 0);
                            $sisa = (int) ($summary['sisaBulanLaporan'] ?? 0);
                            $selesai = isset($summary['perkaraSelesai']) ? (int) $summary['perkaraSelesai'] : max(0, $jumlah - $sisa);

                            $cases[] = [
                                'id'                   => (string) $form->id,
                                'case_index'           => $caseIndex,
                                'satuanKerja'          => $summary['satuanKerja'] ?? 'Kejari Banda Aceh',
                                'kategoriTindakPidana' => $summary['kategoriTindakPidana'] ?? '-',
                                'sisaBulanLalu'        => (int) ($summary['sisaBulanLalu'] ?? 0),
                                'masukBulanLaporan'    => (int) ($summary['masukBulanLaporan'] ?? 0),
                                'jumlahBulanLaporan'   => $jumlah,
                                'perkaraSelesai'       => $selesai,
                                'sisaBulanLaporan'     => $sisa,
                                'keterangan'           => $summary['keterangan'] ?? '-',
                            ];
                        }
                    }
                }
            } else {
                // 2. Jika tidak ada data di DB, hitung kalkulasi otomatis dari Form 3A & 3C
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
            }
        } else {
            // Form 3A & 3C
            $formsQuery = FormTemplate::where('form_type', $formType)
                ->where('month', $month)
                ->where('year', $year)
                ->get();

            foreach ($formsQuery as $form) {
                $allCases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

                if (is_string($allCases)) {
                    $allCases = json_decode($allCases, true);
                }

                if (is_array($allCases)) {
                    foreach ($allCases as $caseIndex => $summary) {
                        $kategoriCase = $summary['kategoriTindakPidana'] ?? '';

                        if ($kategori !== 'ALL' && !$this->matchCategory($kategoriCase, $kategori)) {
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
            
            if (is_string($allCases)) {
                $allCases = json_decode($allCases, true);
            }

            if (is_array($allCases)) {
                foreach ($allCases as $summary) {
                    $kategoriCase = $summary['kategoriTindakPidana'] ?? '';
                    if ($kategori !== 'ALL' && !$this->matchCategory($kategoriCase, $kategori)) {
                        continue;
                    }
                    $count++;
                }
            }
        }

        return $count;
    }

    private function getSisaBulanLalu(int $targetMonth, int $targetYear, string $kategori): int
    {
        $latest3B = FormTemplate::where('form_type', '3B')
            ->where(function ($q) use ($targetYear, $targetMonth) {
                $q->where('year', '<', $targetYear)
                  ->orWhere(function ($q2) use ($targetYear, $targetMonth) {
                      $q2->where('year', $targetYear)->where('month', '<', $targetMonth);
                  });
            })
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        $runningSisa = 0;

        if ($latest3B) {
            $curY = (int) $latest3B->year;
            $curM = (int) $latest3B->month;

            $cases = $latest3B->cases ?? [];
            if (is_string($cases)) {
                $cases = json_decode($cases, true);
            }
            if (is_array($cases)) {
                foreach ($cases as $c) {
                    $kat = $c['kategoriTindakPidana'] ?? '';
                    if ($this->matchCategory($kat, $kategori)) {
                        $runningSisa += (int) ($c['sisaBulanLaporan'] ?? 0);
                    }
                }
            }

            $curM++;
            if ($curM > 12) {
                $curM = 1;
                $curY++;
            }
        } else {
            $earliestForm = FormTemplate::orderBy('year', 'asc')->orderBy('month', 'asc')->first();
            if (!$earliestForm) {
                return 0;
            }
            $curY = (int) $earliestForm->year;
            $curM = (int) $earliestForm->month;
        }

        while ($curY < $targetYear || ($curY === $targetYear && $curM < $targetMonth)) {
            $current3B = FormTemplate::where('form_type', '3B')
                ->where('month', $curM)
                ->where('year', $curY)
                ->first();

            if ($current3B) {
                $cases = $current3B->cases ?? [];
                if (is_string($cases)) {
                    $cases = json_decode($cases, true);
                }
                $runningSisa = 0;
                if (is_array($cases)) {
                    foreach ($cases as $c) {
                        $kat = $c['kategoriTindakPidana'] ?? '';
                        if ($this->matchCategory($kat, $kategori)) {
                            $runningSisa += (int) ($c['sisaBulanLaporan'] ?? 0);
                        }
                    }
                }
            } else {
                $masuk = $this->countCasesByPeriod('3A', $curM, $curY, $kategori);
                $selesai = $this->countCasesByPeriod('3C', $curM, $curY, $kategori);

                $jumlah = $runningSisa + $masuk;
                $runningSisa = max(0, $jumlah - $selesai);
            }

            $curM++;
            if ($curM > 12) {
                $curM = 1;
                $curY++;
            }
        }

        return $runningSisa;
    }

    private function matchCategory(string $raw, string $target): bool
    {
        $cleanRaw = strtoupper(trim($raw));
        $cleanTarget = strtoupper(trim($target));

        if ($cleanTarget === 'ALL') return true;

        if (str_contains($cleanRaw, 'NARKOTIKA') && str_contains($cleanTarget, 'NARKOTIKA')) return true;
        if (str_contains($cleanRaw, 'KAMNEGTIBUM') && str_contains($cleanTarget, 'KAMNEGTIBUM')) return true;
        if (str_contains($cleanRaw, 'OHARDA') && str_contains($cleanTarget, 'OHARDA')) return true;
        if (str_contains($cleanRaw, 'TERORIS') && str_contains($cleanTarget, 'TERORIS')) return true;
        if (str_contains($cleanRaw, 'KORUPSI') && str_contains($cleanTarget, 'KORUPSI')) return true;

        return $cleanRaw === $cleanTarget;
    }

    public function Laporan(Request $request)
    {
        $data = $this->getLaporanData($request);
        return Inertia::render('Tabs/Laporan', $data);
    }

    public function exportDocx(Request $request, LaporanDocxService $docxService)
    {
        $formType = strtoupper($request->input('formType', '3A'));

        if (!in_array($formType, ['3D', '3E', '3F']) && $request->input('kategori', 'ALL') === 'ALL') {
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
        $formType = strtoupper($request->input('formType', '3A'));
        $kategori = $request->input('kategori', 'ALL');

        if (!in_array($formType, ['3D', '3E', '3F']) && $kategori === 'ALL') {
            return back()->with('error', 'Silakan pilih kategori spesifik terlebih dahulu.');
        }

        try {
            $data = $this->getLaporanData($request);

            // 1. Generate dokumen Word (.docx) sementara
            $phpWord = $docxService->build($data);
            $tempId = 'pdf_conv_' . uniqid();
            
            $tempDocxPath = storage_path("app/{$tempId}.docx");
            $tempPdfPath  = storage_path("app/{$tempId}.pdf");
            $outputDir    = storage_path('app');

            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($tempDocxPath);

            if (!file_exists($tempDocxPath)) {
                return back()->with('error', 'Gagal membuat file dokumen Word sementara.');
            }

            // 2. Eksekusi menggunakan Symfony Process
            $libreOfficePath = 'soffice';
            $workingDir = null;

            if (PHP_OS_FAMILY === 'Windows') {
                $workingDir = 'C:\Program Files\LibreOffice\program';
                if (!is_dir($workingDir) && is_dir('C:\Program Files (x86)\LibreOffice\program')) {
                    $workingDir = 'C:\Program Files (x86)\LibreOffice\program';
                }
                
                $libreOfficePath = $workingDir . '\soffice.exe'; 
            }

            $process = new \Symfony\Component\Process\Process([
                $libreOfficePath,
                '--headless',
                '--convert-to',
                'pdf',
                $tempDocxPath,
                '--outdir',
                $outputDir
            ]);

            if ($workingDir) {
                $process->setWorkingDirectory($workingDir);
            }

            $process->setTimeout(120);
            $process->run();

            // 3. Pembersihan (Cleanup) file DOCX
            if (file_exists($tempDocxPath)) {
                @unlink($tempDocxPath);
            }

            // 4. Validasi hasil
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

            return response()->file($tempPdfPath, [
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