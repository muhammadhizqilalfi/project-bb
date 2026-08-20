<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Cell as CellStyle;
use PhpOffice\PhpWord\Style\Table as TableStyle;

class LaporanDocxService
{
    // Ukuran kertas FLSA / Folio Amerika (13 x 8.5 inch)
    private const PAGE_WIDTH_IN = 13.0;
    private const PAGE_HEIGHT_IN = 8.5;

    // Margin halaman (1 cm)
    private const MARGIN_CM = 1.0;

    // Font utama dokumen
    private const FONT_NAME = 'Arial';

    // Ukuran font header halaman
    private const HEADER_FONT_SIZE = 12;

    // Ukuran font judul utama
    private const TITLE_FONT_SIZE = 14;

    // Pengaturan Form 3A
    private const FORM_3A_TABLE_FONT_SIZE = 9;
    private const FORM_3A_ROW_HEIGHT_CM = 0.4;

    // Pengaturan Form 3B
    private const FORM_3B_TABLE_FONT_SIZE = 9;
    private const FORM_3B_ROW_HEIGHT_CM = 0.4;

    // Pengaturan Form 3C
    private const FORM_3C_TABLE_FONT_SIZE = 7.5;
    private const FORM_3C_ROW_HEIGHT_CM = 0.3;

    // Download file DOCX
    public function download(array $data, string $fileName)
    {
        // Build dokumen PhpWord
        $phpWord = $this->build($data);

        // Simpan sementara ke storage lokal
        $tempFileName = 'temp_' . uniqid() . '.docx';
        $tempPath = storage_path('app/' . $tempFileName);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        // Bersihkan output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Stream download file & hapus otomatis setelah dikirim
        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    // Merakit struktur dokumen Word
    public function build(array $data): PhpWord
    {
        // Set folder temporary storage & aktifkan XML output escaping
        Settings::setTempDir(storage_path('app'));
        Settings::setOutputEscapingEnabled(true);

        $phpWord = new PhpWord();

        // Default font dan paragraf
        $phpWord->setDefaultFontName(self::FONT_NAME);
        $phpWord->setDefaultFontSize(self::FORM_3A_TABLE_FONT_SIZE);
        $phpWord->setDefaultFontColor('000000');

        $phpWord->setDefaultParagraphStyle([
            'spaceAfter' => 0,
            'spaceBefore' => 0,
            'lineHeight' => 1.0,
        ]);

        // Pengaturan ukuran halaman FLSA Landscape
        $pageWidth = Converter::inchToTwip(self::PAGE_WIDTH_IN);
        $pageHeight = Converter::inchToTwip(self::PAGE_HEIGHT_IN);
        $margin = Converter::cmToTwip(self::MARGIN_CM);

        $tableWidth = $pageWidth - (2 * $margin);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'pageSizeW' => $pageWidth,
            'pageSizeH' => $pageHeight,
            'marginTop' => $margin,
            'marginBottom' => $margin,
            'marginLeft' => $margin,
            'marginRight' => $margin,
        ]);

        // Parameter filter
        $formType = strtoupper($data['filters']['formType'] ?? '3A');
        $kategori = strtoupper($data['filters']['kategori'] ?? '');
        $month = (int) ($data['filters']['month'] ?? now()->month);
        $year = (int) ($data['filters']['year'] ?? now()->year);

        // Konversi nama bulan
        $monthTranslation = [
            1 => 'JANUARI',   2 => 'FEBRUARI', 3 => 'MARET',     4 => 'APRIL',
            5 => 'MEI',       6 => 'JUNI',     7 => 'JULI',      8 => 'AGUSTUS',
            9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER',
        ];

        $monthName = $monthTranslation[$month] ?? '';

        // Kop Header Halaman
        $this->addPageHeader($section, $formType, $tableWidth);

        // Spasi kecil setelah Kop Header
        $this->addParagraphSpace($section, 0.15);

        // Header Judul Utama
        $titleRun = $section->addTextRun([
            'alignment'   => 'center',
            'spaceBefore' => 0,
            'spaceAfter'  => 0,
            'lineHeight'  => 1.0,
        ]);

        $titleFontStyle = [
            'name' => self::FONT_NAME,
            'bold' => true,
            'size' => self::TITLE_FONT_SIZE,
        ];

        if ($formType === '3A') {
            $titleLine1 = ($kategori === 'KORUPSI')
                ? 'LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS'
                : 'LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM';

            $titleRun->addText($titleLine1, $titleFontStyle);
            $titleRun->addTextBreak();
            $titleRun->addText('BULAN ' . $monthName . ' ' . $year, $titleFontStyle);
            $titleRun->addTextBreak();
            $titleRun->addText(': T.P. ' . ($data['filters']['kategori'] ?? '-'), $titleFontStyle);
        } elseif ($formType === '3B') {
            // Form 3B: Judul Laporan Penyelesaian Barang Bukti
            $titleLine1 = ($kategori === 'KORUPSI')
                ? 'LAPORAN PENYELESAIAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS'
                : 'LAPORAN PENYELESAIAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM';

            $titleRun->addText($titleLine1, $titleFontStyle);
            $titleRun->addTextBreak();
            $titleRun->addText('BULAN ' . $monthName . ' ' . $year, $titleFontStyle);
            $titleRun->addTextBreak();
            $titleRun->addText(': T.P. ' . ($data['filters']['kategori'] ?? '-'), $titleFontStyle);
        } else {
            // Form 3C
            $titleLine1 = ($kategori === 'KORUPSI')
                ? 'LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS YANG SUDAH MEMPEROLEH'
                : 'LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM YANG SUDAH MEMPEROLEH';
            $titleLine2 = 'KEKUATAN HUKUM TETAP DARI PENGADILAN';

            $titleRun->addText($titleLine1, $titleFontStyle);
            $titleRun->addTextBreak();
            $titleRun->addText($titleLine2, $titleFontStyle);
            $titleRun->addTextBreak();
            $titleRun->addText('BULAN ' . $monthName . ' ' . $year, $titleFontStyle);
            $titleRun->addTextBreak();
            $titleRun->addText(': T.P. ' . ($data['filters']['kategori'] ?? '-'), $titleFontStyle);
        }

        // Spasi sebelum Tabel Data
        $this->addParagraphSpace($section, 0.12);

        // Konstruksi Tabel Data Utama
        if ($formType === '3A') {
            $this->addForm3ATable($section, $data['cases'] ?? [], $tableWidth);
        } elseif ($formType === '3B') {
            $this->addForm3BTable($section, $data['cases'] ?? [], $tableWidth);
        } elseif ($formType === '3C') {
            $this->addForm3CTable($section, $data['cases'] ?? [], $tableWidth);
        }

        // Spasi sebelum Tanda Tangan
        $this->addParagraphSpace($section, 2);

        // Seksi Tanda Tangan
        $this->addSignature($section, $tableWidth);

        return $phpWord;
    }

    // Kop Header Halaman dengan Garis Bawah Tebal
    private function addPageHeader(
        Section $section,
        string $formType,
        int $tableWidth
    ): void {
        $header = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
        ]);

        $row = $header->addRow();

        $noBorderStyle = [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'valign' => 'top',
        ];

        // Kolom Kiri KOP
        $left = $row->addCell((int) round($tableWidth * 0.2), $noBorderStyle);

        $leftInnerTable = $left->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
        ]);

        $innerRow = $leftInnerTable->addRow();
        $innerCell = $innerRow->addCell(
            (int) round($tableWidth * 0.2),
            [
                'borderBottomSize'  => 20, // Underline Tebal (2.5 pt)
                'borderBottomColor' => '000000',
            ]
        );

        $innerCell->addText(
            'KEJAKSAAN NEGERI',
            [
                'name' => self::FONT_NAME,
                'bold' => true,
                'size' => self::HEADER_FONT_SIZE,
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );

        $innerCell->addText(
            'BANDA ACEH',
            [
                'name' => self::FONT_NAME,
                'bold' => true,
                'size' => self::HEADER_FONT_SIZE,
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );

        // Kolom Kanan KOP
        $right = $row->addCell((int) round($tableWidth * 0.8), $noBorderStyle);

        $right->addText(
            'FORM ' . $formType,
            [
                'name' => self::FONT_NAME,
                'bold' => true,
                'size' => self::HEADER_FONT_SIZE,
            ],
            [
                'alignment' => 'right',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );
    }

    // Tabel Form 3A (11 Kolom)
    private function addForm3ATable(
        Section $section,
        array $cases,
        int $tableWidth
    ): void {
        $widths = [3, 8, 10, 9, 16, 9, 11, 10, 7, 9, 8];
        $twips = $this->columnWidths($widths, $tableWidth);

        $table = $section->addTable([
            'borderTopSize'     => 6,
            'borderTopColor'    => '000000',
            'borderBottomSize'  => 6,
            'borderBottomColor' => '000000',
            'borderLeftSize'    => 6,
            'borderLeftColor'   => '000000',
            'borderRightSize'   => 6,
            'borderRightColor'  => '000000',
            'borderInsideVSize' => 6,
            'borderInsideVColor'=> '000000',
            'borderInsideHSize' => 0,
            'cellMargin'        => 35,
            'layout'            => TableStyle::LAYOUT_FIXED,
            'width'             => $tableWidth,
            'unit'              => TblWidth::TWIP,
        ]);

        $headerStyle = [
            'name' => self::FONT_NAME,
            'bold' => true,
            'size' => self::FORM_3A_TABLE_FONT_SIZE,
        ];

        $headerCellStyle = [
            'borderSize' => 6,
            'valign'     => 'center',
            'bgColor'    => 'FFFFFF',
        ];

        // Baris Header Kolom
        $row = $this->addTableRow($table, self::FORM_3A_ROW_HEIGHT_CM, true);
        $headers = [
            'No. Urut',
            'Satuan Kerja',
            'Register Benda Sitaan Barang Bukti',
            'Register Tahap Penyidikan',
            'Uraian Benda Sitaan Jumlah / Satuan / Jenis / Barang / Ukuran',
            'Tempat Penyimpanan',
            'Identitas Tersangka / Terdakwa',
            'Pasal yang disangkakan / didakwakan',
            'Diselesaikan',
            'Tanggal Pelaksanaan Putusan Hakim & Ijin Jaksa Agung',
            'Keterangan',
        ];

        foreach ($headers as $i => $header) {
            $cell = $row->addCell($twips[$i], $headerCellStyle);
            $this->addCellText($cell, $header, $headerStyle, 'center', 'center');
        }

        // Baris Nomor Urut Kolom (1-11)
        $row = $this->addTableRow($table, self::FORM_3A_ROW_HEIGHT_CM, true);
        for ($i = 1; $i <= 11; $i++) {
            $cell = $row->addCell($twips[$i - 1], $headerCellStyle);
            $this->addCellText($cell, (string) $i, $headerStyle, 'center', 'center');
        }

        // Data Kosong (NIHIL)
        if (empty($cases)) {
            $row = $this->addTableRow($table, self::FORM_3A_ROW_HEIGHT_CM, false);
            $cell = $row->addCell($tableWidth, $headerCellStyle);
            $cell->getStyle()->setGridSpan(11);
            $this->addCellText($cell, 'NIHIL', ['name' => self::FONT_NAME, 'bold' => true, 'size' => 28], 'center', 'center');
            return;
        }

        // Perulangan Data Perkara
        foreach ($cases as $idx => $case) {
            $bbList = !empty($case['barangBuktiList']) ? $case['barangBuktiList'] : [null];
            $totalBb = count($bbList);

            foreach ($bbList as $bIdx => $bb) {
                $row = $this->addTableRow($table, self::FORM_3A_ROW_HEIGHT_CM, false);

                $isLastBb = ($bIdx === $totalBb - 1);

                $bbBorderStyle = $isLastBb ? [
                    'borderBottomSize'  => 6,
                    'borderBottomColor' => '000000',
                ] : [];

                $mergeState = $bIdx === 0 ? CellStyle::VMERGE_RESTART : CellStyle::VMERGE_CONTINUE;

                $this->addMergedCell($row, $twips[0], $bIdx === 0 ? ($idx + 1) . '.' : '', $mergeState, 'center', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);
                $this->addMergedCell($row, $twips[1], $bIdx === 0 ? ($case['satuanKerja'] ?? '-') : '', $mergeState, 'center', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);
                $this->addMergedCell($row, $twips[2], $bIdx === 0 ? $this->withDate($case['noRegBendaSitaan'] ?? '-', $case['tglPenerimaan'] ?? null) : '', $mergeState, 'center', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);
                $this->addMergedCell($row, $twips[3], $bIdx === 0 ? $this->withDate($case['noRegPenyidikan'] ?? '-', $case['tglRegPenyidikan'] ?? null) : '', $mergeState, 'center', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);

                $satuanText = (!empty($bb['satuan']) && trim($bb['satuan']) !== '-') ? trim($bb['satuan']) . ' ' : '';
                $uraianText = $bb['uraianBarangBukti'] ?? $bb['jenisBarangBukti'] ?? '-';
                $uraian = $bb
                    ? '- ' . $this->formatJumlah($bb['jumlah'] ?? null) . ' ' . $satuanText . $uraianText
                    : '-';
                $this->addPlainCell($row, $twips[4], $uraian, 'left', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);

                $currentLoc = !empty($bb['tempatPenyimpanan']) ? trim($bb['tempatPenyimpanan']) : '-';
                $prevLoc    = ($bIdx > 0 && !empty($bbList[$bIdx - 1]['tempatPenyimpanan'])) ? trim($bbList[$bIdx - 1]['tempatPenyimpanan']) : null;
                $nextLoc    = ($bIdx < $totalBb - 1 && !empty($bbList[$bIdx + 1]['tempatPenyimpanan'])) ? trim($bbList[$bIdx + 1]['tempatPenyimpanan']) : null;

                $isLocStart = ($bIdx === 0 || $currentLoc !== $prevLoc);
                $isLocEnd   = ($bIdx === $totalBb - 1 || $currentLoc !== $nextLoc);

                $locMergeState = $isLocStart ? CellStyle::VMERGE_RESTART : CellStyle::VMERGE_CONTINUE;
                $locText       = $isLocStart ? $currentLoc : '';

                $locBorderStyle = ($isLastBb || $isLocEnd) ? [
                    'borderBottomSize'  => 6,
                    'borderBottomColor' => '000000',
                ] : [];

                $this->addMergedCell($row, $twips[5], $locText, $locMergeState, 'center', 'center', self::FORM_3A_TABLE_FONT_SIZE, $locBorderStyle);
                $this->addMergedCell($row, $twips[6], $bIdx === 0 ? ($case['identitasTersangka'] ?? '-') : '', $mergeState, 'left', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);
                $this->addMergedCell($row, $twips[7], $bIdx === 0 ? ($case['pasalDisangkakan'] ?? '-') : '', $mergeState, 'left', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);
                $this->addMergedCell($row, $twips[8], $bIdx === 0 ? ($case['statusDiselesaikan'] ?? '-') : '', $mergeState, 'center', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);
                $this->addMergedCell($row, $twips[9], $bIdx === 0 ? ($case['tglPelaksanaanPutusan'] ?? '-') : '', $mergeState, 'center', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);
                $this->addMergedCell($row, $twips[10], $bIdx === 0 ? ($case['keterangan'] ?? '-') : '', $mergeState, 'center', 'top', self::FORM_3A_TABLE_FONT_SIZE, $bbBorderStyle);
            }
        }
    }

    // Tabel Form 3B (7 Kolom dengan Multilevel Header Presisi Gambar Acuan)
    private function addForm3BTable(
        Section $section,
        array $cases,
        int $tableWidth
    ): void {
        // Persentase Lebar 7 Kolom
        $widths = [6, 30, 13, 13, 13, 12, 13];
        $twips = $this->columnWidths($widths, $tableWidth);

        $table = $section->addTable([
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 35,
            'layout'      => TableStyle::LAYOUT_FIXED,
            'width'       => $tableWidth,
            'unit'        => TblWidth::TWIP,
        ]);

        $headerStyle = [
            'name' => self::FONT_NAME,
            'bold' => true,
            'size' => self::FORM_3B_TABLE_FONT_SIZE,
        ];

        $headerCellStyle = [
            'borderSize' => 6,
            'valign'     => 'center',
            'bgColor'    => 'FFFFFF',
        ];

        // BARIS HEADER 1 (Tingkat Atas)
        $row1 = $this->addTableRow($table, self::FORM_3B_ROW_HEIGHT_CM, true);

        // 1. No. Urut (Vertical Merge Start)
        $cell = $row1->addCell($twips[0], array_merge($headerCellStyle, ['vMerge' => CellStyle::VMERGE_RESTART]));
        $this->addCellText($cell, "No.\nUrut", $headerStyle, 'center', 'center');

        // 2. Kejaksaan (Vertical Merge Start)
        $cell = $row1->addCell($twips[1], array_merge($headerCellStyle, ['vMerge' => CellStyle::VMERGE_RESTART]));
        $this->addCellText($cell, 'Kejaksaan', $headerStyle, 'center', 'center');

        // 3-5. Sel Kosong Penggabung di atas Kolom 3, 4, 5 (Horizontal Span 3 Kolom)
        $groupWidth = $twips[2] + $twips[3] + $twips[4];
        $cell = $row1->addCell($groupWidth, array_merge($headerCellStyle, ['gridSpan' => 3]));
        $this->addCellText($cell, ' ', $headerStyle, 'center', 'center');

        // 6. Sisa Bulan Laporan (Vertical Merge Start)
        $cell = $row1->addCell($twips[5], array_merge($headerCellStyle, ['vMerge' => CellStyle::VMERGE_RESTART]));
        $this->addCellText($cell, "Sisa Bulan\nLaporan", $headerStyle, 'center', 'center');

        // 7. Keterangan (Vertical Merge Start)
        $cell = $row1->addCell($twips[6], array_merge($headerCellStyle, ['vMerge' => CellStyle::VMERGE_RESTART]));
        $this->addCellText($cell, 'Keterangan', $headerStyle, 'center', 'center');


        // BARIS HEADER 2 (Sub-Header)
        $row2 = $this->addTableRow($table, self::FORM_3B_ROW_HEIGHT_CM, true);

        // 1. No. Urut (Vertical Merge Continue)
        $row2->addCell($twips[0], array_merge($headerCellStyle, ['vMerge' => CellStyle::VMERGE_CONTINUE]));

        // 2. Kejaksaan (Vertical Merge Continue)
        $row2->addCell($twips[1], array_merge($headerCellStyle, ['vMerge' => CellStyle::VMERGE_CONTINUE]));

        // 3. Sisa Bulan Lalu
        $cell = $row2->addCell($twips[2], $headerCellStyle);
        $this->addCellText($cell, "Sisa Bulan\nLalu", $headerStyle, 'center', 'center');

        // 4. Masuk Bulan Laporan
        $cell = $row2->addCell($twips[3], $headerCellStyle);
        $this->addCellText($cell, "Masuk Bulan\nLaporan", $headerStyle, 'center', 'center');

        // 5. Jumlah Bulan Laporan
        $cell = $row2->addCell($twips[4], $headerCellStyle);
        $this->addCellText($cell, "Jumlah Bulan\nLaporan", $headerStyle, 'center', 'center');

        // 6. Sisa Bulan Laporan (Vertical Merge Continue)
        $row2->addCell($twips[5], array_merge($headerCellStyle, ['vMerge' => CellStyle::VMERGE_CONTINUE]));

        // 7. Keterangan (Vertical Merge Continue)
        $row2->addCell($twips[6], array_merge($headerCellStyle, ['vMerge' => CellStyle::VMERGE_CONTINUE]));


        // BARIS HEADER 3 (Nomor Urut Kolom 1-7)
        $row3 = $this->addTableRow($table, self::FORM_3B_ROW_HEIGHT_CM, true);
        for ($i = 1; $i <= 7; $i++) {
            $cell = $row3->addCell($twips[$i - 1], $headerCellStyle);
            $this->addCellText($cell, (string) $i, $headerStyle, 'center', 'center');
        }

        // Data Kosong (NIHIL)
        if (empty($cases)) {
            $row = $this->addTableRow($table, self::FORM_3B_ROW_HEIGHT_CM, false);
            $cell = $row->addCell($tableWidth, $headerCellStyle);
            $cell->getStyle()->setGridSpan(7);
            $this->addCellText($cell, 'NIHIL', ['name' => self::FONT_NAME, 'bold' => true, 'size' => 28], 'center', 'center');
            return;
        }

        // Perulangan Data Form 3B
        foreach ($cases as $idx => $case) {
            $row = $this->addTableRow($table, self::FORM_3B_ROW_HEIGHT_CM, false);
            $cellStyle = ['borderSize' => 6, 'borderColor' => '000000', 'valign' => 'top'];

            $this->addPlainCell($row, $twips[0], ($idx + 1) . '.', 'center', 'top', self::FORM_3B_TABLE_FONT_SIZE, $cellStyle);
            $this->addPlainCell($row, $twips[1], $case['satuanKerja'] ?? 'Kejari Banda Aceh', 'left', 'top', self::FORM_3B_TABLE_FONT_SIZE, $cellStyle);
            $this->addPlainCell($row, $twips[2], (string) ($case['sisaBulanLalu'] ?? '0'), 'center', 'top', self::FORM_3B_TABLE_FONT_SIZE, $cellStyle);
            $this->addPlainCell($row, $twips[3], (string) ($case['masukBulanLaporan'] ?? '0'), 'center', 'top', self::FORM_3B_TABLE_FONT_SIZE, $cellStyle);
            $this->addPlainCell($row, $twips[4], (string) ($case['jumlahBulanLaporan'] ?? '0'), 'center', 'top', self::FORM_3B_TABLE_FONT_SIZE, $cellStyle);
            $this->addPlainCell($row, $twips[5], (string) ($case['sisaBulanLaporan'] ?? '0'), 'center', 'top', self::FORM_3B_TABLE_FONT_SIZE, $cellStyle);
            $this->addPlainCell($row, $twips[6], $case['keterangan'] ?? '-', 'center', 'top', self::FORM_3B_TABLE_FONT_SIZE, $cellStyle);
        }
    }

    // Tabel Form 3C (12 Kolom)
    private function addForm3CTable(
        Section $section,
        array $cases,
        int $tableWidth
    ): void {
        $widths = [3, 7, 18, 9, 8, 7, 6, 6, 8, 8, 12, 8];
        $twips = $this->columnWidths($widths, $tableWidth);

        $table = $section->addTable([
            'borderTopSize'     => 6,
            'borderTopColor'    => '000000',
            'borderBottomSize'  => 6,
            'borderBottomColor' => '000000',
            'borderLeftSize'    => 6,
            'borderLeftColor'   => '000000',
            'borderRightSize'   => 6,
            'borderRightColor'  => '000000',
            'borderInsideVSize' => 6,
            'borderInsideVColor'=> '000000',
            'borderInsideHSize' => 0,
            'cellMargin'        => 30,
            'layout'            => TableStyle::LAYOUT_FIXED,
            'width'             => $tableWidth,
            'unit'              => TblWidth::TWIP,
        ]);

        $headerStyle = [
            'name' => self::FONT_NAME,
            'bold' => true,
            'size' => self::FORM_3C_TABLE_FONT_SIZE,
        ];

        $headerCellStyle = [
            'borderSize' => 6,
            'valign'     => 'center',
            'bgColor'    => 'FFFFFF',
        ];

        // Baris Header Kolom
        $headers = [
            'No. Urut',
            'Kejaksaan',
            'Jenis Barang Bukti',
            'Pasal Yang Didakwakan',
            'Register Benda Sitaan Barang Bukti / Tanggal Penerimaan Barang Bukti',
            'Macam Jenis Kadar',
            'Jumlah Satuan',
            'Jenis Satuan',
            'Tempat Penyimpanan',
            'Tgl & No. KEP PN/PT/MA',
            'Amar Putusan',
            'Tanggal Pelaksanaan Putusan Hakim',
        ];

        $row = $this->addTableRow($table, self::FORM_3C_ROW_HEIGHT_CM, true);
        foreach ($headers as $i => $header) {
            $cell = $row->addCell($twips[$i], $headerCellStyle);
            $this->addCellText($cell, $header, $headerStyle, 'center', 'center');
        }

        // Baris Nomor Urut Kolom (1-12)
        $row = $this->addTableRow($table, self::FORM_3C_ROW_HEIGHT_CM, true);
        for ($i = 1; $i <= 12; $i++) {
            $cell = $row->addCell($twips[$i - 1], $headerCellStyle);
            $this->addCellText($cell, (string) $i, $headerStyle, 'center', 'center');
        }

        // Data Kosong (NIHIL)
        if (empty($cases)) {
            $row = $this->addTableRow($table, self::FORM_3C_ROW_HEIGHT_CM, false);
            $cell = $row->addCell($tableWidth, $headerCellStyle);
            $cell->getStyle()->setGridSpan(12);
            $this->addCellText($cell, 'NIHIL', ['name' => self::FONT_NAME, 'bold' => true, 'size' => 28], 'center', 'center');
            return;
        }

        // Perulangan Data Perkara
        foreach ($cases as $idx => $case) {
            $bbList = !empty($case['barangBuktiList']) ? $case['barangBuktiList'] : [null];
            $totalBb = count($bbList);

            foreach ($bbList as $bIdx => $bb) {
                $row = $this->addTableRow($table, self::FORM_3C_ROW_HEIGHT_CM, false);

                $isLastBb = ($bIdx === $totalBb - 1);

                $bbBorderStyle = $isLastBb ? [
                    'borderBottomSize'  => 6,
                    'borderBottomColor' => '000000',
                ] : [];

                $mergeState = $bIdx === 0 ? CellStyle::VMERGE_RESTART : CellStyle::VMERGE_CONTINUE;

                $this->addMergedCell($row, $twips[0], $bIdx === 0 ? ($idx + 1) . '.' : '', $mergeState, 'center', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);
                $this->addMergedCell($row, $twips[1], $bIdx === 0 ? ($case['satuanKerja'] ?? '-') : '', $mergeState, 'center', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $satuanText = (!empty($bb['satuan']) && trim($bb['satuan']) !== '-') ? trim($bb['satuan']) . ' ' : ((!empty($bb['jenisSatuan']) && trim($bb['jenisSatuan']) !== '-') ? trim($bb['jenisSatuan']) . ' ' : '');
                $uraianText = $bb['jenisBarangBukti'] ?? $bb['uraianBarangBukti'] ?? '-';
                $jenis = $bb
                    ? '- ' . $this->formatJumlah($bb['jumlah'] ?? $bb['jumlahSatuan'] ?? null) . ' ' . $satuanText . $uraianText
                    : '-';
                $this->addPlainCell($row, $twips[2], $jenis, 'left', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $this->addMergedCell($row, $twips[3], $bIdx === 0 ? ($case['pasalDidakwakan'] ?? '-') : '', $mergeState, 'left', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $register = $bIdx === 0 ? $this->withDate($case['noRegBendaSitaan'] ?? '-', $case['tglPenerimaan'] ?? null) : '';
                $this->addMergedCell($row, $twips[4], $register, $mergeState, 'center', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $kadarText = ($bb && !empty($bb['macamJenisKadar']) && $bb['macamJenisKadar'] !== '-') ? '• ' . $bb['macamJenisKadar'] : '-';
                $this->addPlainCell($row, $twips[5], $kadarText, 'left', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $this->addPlainCell($row, $twips[6], '• ' . $this->formatJumlah($bb['jumlah'] ?? $bb['jumlahSatuan'] ?? null), 'left', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $jenisSatuanText = ($bb && (!empty($bb['satuan']) || !empty($bb['jenisSatuan']))) ? '• ' . ($bb['satuan'] ?? $bb['jenisSatuan']) : '-';
                $this->addPlainCell($row, $twips[7], $jenisSatuanText, 'left', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $currentLoc = !empty($bb['tempatPenyimpanan']) ? trim($bb['tempatPenyimpanan']) : '-';
                $prevLoc    = ($bIdx > 0 && !empty($bbList[$bIdx - 1]['tempatPenyimpanan'])) ? trim($bbList[$bIdx - 1]['tempatPenyimpanan']) : null;
                $nextLoc    = ($bIdx < $totalBb - 1 && !empty($bbList[$bIdx + 1]['tempatPenyimpanan'])) ? trim($bbList[$bIdx + 1]['tempatPenyimpanan']) : null;

                $isLocStart = ($bIdx === 0 || $currentLoc !== $prevLoc);
                $isLocEnd   = ($bIdx === $totalBb - 1 || $currentLoc !== $nextLoc);

                $locMergeState = $isLocStart ? CellStyle::VMERGE_RESTART : CellStyle::VMERGE_CONTINUE;
                $locText       = $isLocStart ? $currentLoc : '';

                $locBorderStyle = ($isLastBb || $isLocEnd) ? [
                    'borderBottomSize'  => 6,
                    'borderBottomColor' => '000000',
                ] : [];

                $this->addMergedCell($row, $twips[8], $locText, $locMergeState, 'center', 'center', self::FORM_3C_TABLE_FONT_SIZE, $locBorderStyle);

                $kep = $bIdx === 0 ? $this->withDate($case['noKepPengadilan'] ?? '-', $case['tglKepPengadilan'] ?? null) : '';
                $this->addMergedCell($row, $twips[9], $kep, $mergeState, 'center', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $amar = $this->amarPutusan($bbList, $bIdx);
                $this->addPlainCell($row, $twips[10], $amar, 'left', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);

                $tgl = $bIdx === 0 ? $this->formatDate($case['tglPelaksanaanPutusan'] ?? null) : '';
                $this->addMergedCell($row, $twips[11], $tgl, $mergeState, 'center', 'top', self::FORM_3C_TABLE_FONT_SIZE, $bbBorderStyle);
            }
        }
    }

    // Seksi Tanda Tangan
    private function addSignature(
        Section $section,
        int $tableWidth
    ): void {
        $setting = Setting::where('key', 'pejabat_kasi')->first();
        $pejabatData = $setting?->value ?? [];

        $jabatan = $pejabatData['jabatan_kasi'] ?? 'KEPALA SEKSI PEMULIHAN ASET DAN PENGELOLAAN BARANG BUKTI';
        $nama = $pejabatData['nama_kasi'] ?? '-';
        $nip = $pejabatData['nip_kasi'] ?? '-';
        $pangkat = $pejabatData['pangkat_kasi'] ?? '';

        $table = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
        ]);

        $row = $table->addRow();

        // Kolom Kiri Kosong (60%)
        $row->addCell((int) round($tableWidth * 0.60), ['borderSize' => 0, 'borderColor' => 'FFFFFF']);

        // Kolom Kanan TTD (40%)
        $cell = $row->addCell(
            (int) round($tableWidth * 0.40),
            [
                'borderSize' => 0,
                'borderColor' => 'FFFFFF',
                'valign' => 'top',
            ]
        );

        // Tanggal Laporan
        $cell->addText(
            'Banda Aceh, ' . Carbon::now()->locale('id')->translatedFormat('d F Y'),
            ['name' => self::FONT_NAME, 'size' => 13, 'bold' => false],
            ['alignment' => 'center', 'spaceBefore' => 240, 'spaceAfter' => 0]
        );

        // Jabatan Pejabat
        $textJabatan = str_replace('ASET ', "ASET\n", 'Pth. ' . $jabatan);
        $barisJabatan = explode("\n", $textJabatan);

        foreach ($barisJabatan as $baris) {
            $cell->addText(
                $baris,
                ['name' => self::FONT_NAME, 'size' => 13, 'bold' => true],
                ['alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0]
            );
        }

        // Ruang Kosong Tanda Tangan
        $cell->addText(
            '',
            ['name' => self::FONT_NAME, 'size' => 10],
            ['alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 4.0]
        );

        // Nama Pejabat (Garis Bawah)
        $cell->addText(
            $nama,
            ['name' => self::FONT_NAME, 'bold' => true, 'size' => 13, 'underline' => 'single'],
            ['alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0]
        );

        // Pangkat / NIP Pejabat
        $nipText = $pangkat !== '' ? $pangkat . ' / ' : '';
        $nipText .= 'NIP. ' . $nip;

        $cell->addText(
            $nipText,
            ['name' => self::FONT_NAME, 'bold' => true, 'size' => 13],
            ['alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
    }

    // Helper: Tambah Baris Tabel
    private function addTableRow($table, float $heightCm, bool $isHeader)
    {
        return $table->addRow(
            Converter::cmToTwip($heightCm),
            [
                'cantSplit' => !$isHeader,
                'exactHeight' => false,
            ]
        );
    }

    // Helper: Hitung Lebar Kolom Twip
    private function columnWidths(array $percentages, int $tableWidth): array
    {
        $total = array_sum($percentages);

        return array_map(
            fn ($percent) => (int) round($tableWidth * ($percent / $total)),
            $percentages
        );
    }

    // Helper: Cell Biasa
    private function addPlainCell(
        $row,
        int $width,
        string $text,
        string $align,
        string $valign,
        float $fontSize,
        array $cellStyleOverrides = []
    ): Cell {
        $style = array_merge(['valign' => $valign], $cellStyleOverrides);

        $cell = $row->addCell($width, $style);
        $this->addCellText($cell, $text, ['name' => self::FONT_NAME, 'size' => $fontSize, 'bold' => false], $align, $valign);

        return $cell;
    }

    // Helper: Cell Merged Vertikal
    private function addMergedCell(
        $row,
        int $width,
        string $text,
        string $mergeState,
        string $align,
        string $valign,
        float $fontSize,
        array $cellStyleOverrides = []
    ): Cell {
        $style = array_merge([
            'vMerge' => $mergeState,
            'valign' => $valign,
        ], $cellStyleOverrides);

        $cell = $row->addCell($width, $style);

        if ($mergeState === CellStyle::VMERGE_RESTART) {
            $this->addCellText($cell, $text, ['name' => self::FONT_NAME, 'size' => $fontSize, 'bold' => false], $align, $valign);
        }

        return $cell;
    }

    // Helper: Format Teks Dalam Cell
    private function addCellText(
        Cell $cell,
        string $text,
        array $fontStyle,
        string $align,
        string $valign
    ): void {
        $paragraphStyle = [
            'alignment' => $align,
            'spaceBefore' => 0,
            'spaceAfter' => 0,
            'lineHeight' => 1.0,
        ];

        $parts = preg_split("/\r\n|\r|\n/", (string) $text);

        foreach ($parts as $index => $part) {
            if ($index > 0) {
                $cell->addTextBreak();
            }

            $cell->addText($part !== '' ? $part : ' ', $fontStyle, $paragraphStyle);
        }
    }

    // Helper: Spasi Paragraf Kosong
    private function addParagraphSpace(Section $section, float $heightCm): void
    {
        $section->addText(
            '',
            ['name' => self::FONT_NAME, 'size' => 1],
            ['spaceBefore' => 0, 'spaceAfter' => 0, 'lineHeight' => 1.0]
        );
    }

    // Helper: Gabung Nomor dan Tanggal
    private function withDate(string $number, ?string $date): string
    {
        if (!$date || $date === '-') {
            return $number;
        }

        return $number . "\n" . $this->formatDate($date);
    }

    // Helper: Format Tanggal Indonesia
    private function formatDate(?string $date): string
    {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable) {
            return $date;
        }
    }

    // Helper: Format Jumlah Terbilang
    private function formatJumlah($value): string
    {
        if ($value === null || $value === '' || $value === '-') {
            return '-';
        }

        if (!is_numeric($value)) {
            return (string) $value;
        }

        $angka = (int) $value;

        if ($angka < 0) {
            return (string) $value;
        }

        if ($angka === 0) {
            return '0';
        }

        $baca = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $terbilang = function ($n) use (&$terbilang, $baca) {
            if ($n < 12) {
                return $baca[$n];
            }
            if ($n < 20) {
                return $terbilang($n - 10) . ' belas';
            }
            if ($n < 100) {
                return $terbilang(intdiv($n, 10)) . ' puluh ' . $terbilang($n % 10);
            }
            if ($n < 200) {
                return 'seratus ' . $terbilang($n - 100);
            }
            if ($n < 1000) {
                return $terbilang(intdiv($n, 100)) . ' ratus ' . $terbilang($n % 100);
            }

            return (string) $n;
        };

        $word = trim(preg_replace('/\s+/', ' ', $terbilang($angka)));

        return $value . ' (' . $word . ')';
    }

    // Helper: Format Amar Putusan
    private function amarPutusan(array $bbList, int $index): string
    {
        $bb = $bbList[$index] ?? null;

        if (!$bb) {
            return '-';
        }

        $current = trim(($bb['amarPutusan'] ?? '') . ' ' . ($bb['uraianPutusan'] ?? ''));

        if ($index > 0 && isset($bbList[$index - 1])) {
            $previous = trim(($bbList[$index - 1]['amarPutusan'] ?? '') . ' ' . ($bbList[$index - 1]['uraianPutusan'] ?? ''));

            if ($previous !== '' && $previous === $current) {
                return '• Sda';
            }
        }

        return '• ' . ($current !== '' ? $current : '-');
    }
}