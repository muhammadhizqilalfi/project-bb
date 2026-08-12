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
    /**
     * Ukuran FLSA / Folio Amerika:
     * 13 x 8.5 inch
     */
    private const PAGE_WIDTH_IN = 13.0;
    private const PAGE_HEIGHT_IN = 8.5;

    /**
     * Margin 1 cm.
     */
    private const MARGIN_CM = 1.0;

    /**
     * Font utama seluruh dokumen.
     */
    private const FONT_NAME = 'Arial';

    /**
     * Ukuran font header halaman.
     */
    private const HEADER_FONT_SIZE = 12;

    /**
     * Ukuran font judul.
     */
    private const TITLE_FONT_SIZE = 14;

    /**
     * Form 3A.
     */
    private const FORM_3A_TABLE_FONT_SIZE = 9;
    private const FORM_3A_ROW_HEIGHT_CM = 0.4;

    /**
     * Form 3C.
     */
    private const FORM_3C_TABLE_FONT_SIZE = 7.5;
    private const FORM_3C_ROW_HEIGHT_CM = 0.3;

    /**
     * Download file DOCX.
     */
    public function download(array $data, string $fileName)
    {
        // 1. Set Temp Dir & Aktifkan XML Escaping (Cegah XML Rusak)
        Settings::setTempDir(storage_path('app'));
        Settings::setOutputEscapingEnabled(true);

        // 2. Build Dokumen PhpWord
        $phpWord = $this->build($data);

        // 3. Simpan Sementara ke File Fisik di Storage
        $tempFileName = 'temp_' . uniqid() . '.docx';
        $tempPath = storage_path('app/' . $tempFileName);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempPath);

        // 4. Bersihkan Seluruh Output Buffer sebelum File Dikirim
        while (ob_get_level()) {
            ob_end_clean();
        }

        // 5. Download File Fisik & Hapus Otomatis Setelah Dikirim
        return response()->download($tempPath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Membuat dokumen Word.
     */
    public function build(array $data): PhpWord
    {
        $phpWord = new PhpWord();

        /*
         * =========================================================
         * DEFAULT FONT DAN PARAGRAPH
         * =========================================================
         */

        $phpWord->setDefaultFontName(self::FONT_NAME);
        $phpWord->setDefaultFontSize(self::FORM_3A_TABLE_FONT_SIZE);
        $phpWord->setDefaultFontColor('000000');

        $phpWord->setDefaultParagraphStyle([
            'spaceAfter' => 0,
            'spaceBefore' => 0,
            'lineHeight' => 1.0,
        ]);

        /*
         * =========================================================
         * UKURAN HALAMAN
         * =========================================================
         */

        $pageWidth = Converter::inchToTwip(self::PAGE_WIDTH_IN);
        $pageHeight = Converter::inchToTwip(self::PAGE_HEIGHT_IN);
        $margin = Converter::cmToTwip(self::MARGIN_CM);

        $tableWidth = $pageWidth - (2 * $margin);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',

            /*
             * FLSA = 13 x 8.5 inch.
             */
            'pageSizeW' => $pageWidth,
            'pageSizeH' => $pageHeight,

            /*
             * Margin 1 cm.
             */
            'marginTop' => $margin,
            'marginBottom' => $margin,
            'marginLeft' => $margin,
            'marginRight' => $margin,

            /*
             * Jangan gunakan header/footer Word.
             *
             * Header halaman dibuat sebagai bagian dari isi dokumen
             * sehingga tidak diulang ketika pindah halaman.
             */
        ]);

        /*
         * =========================================================
         * FILTER
         * =========================================================
         */

        $formType = strtoupper(
            $data['filters']['formType'] ?? '3A'
        );

        $kategori = strtoupper(
            $data['filters']['kategori'] ?? ''
        );

        $month = (int) (
            $data['filters']['month']
            ?? now()->month
        );

        $year = (int) (
            $data['filters']['year']
            ?? now()->year
        );

        /*
         * =========================================================
         * NAMA BULAN
         * =========================================================
         */

        $monthTranslation = [
            1 => 'JANUARI',
            2 => 'FEBRUARI',
            3 => 'MARET',
            4 => 'APRIL',
            5 => 'MEI',
            6 => 'JUNI',
            7 => 'JULI',
            8 => 'AGUSTUS',
            9 => 'SEPTEMBER',
            10 => 'OKTOBER',
            11 => 'NOVEMBER',
            12 => 'DESEMBER',
        ];

        $monthName = $monthTranslation[$month] ?? '';

        /*
         * =========================================================
         * HEADER HALAMAN
         * =========================================================
         *
         * Header tidak dibuat sebagai Word Header.
         * Dengan demikian header ini tidak otomatis diulang
         * ketika halaman berikutnya dimulai.
         */

        $this->addPageHeader(
            $section,
            $formType,
            $tableWidth
        );

        /*
         * =========================================================
         * JARAK SETELAH HEADER
         * =========================================================
         */

        $this->addParagraphSpace(
            $section,
            0.15
        );

        /*
         * =========================================================
         * JUDUL
         * =========================================================
         */

        $title = $this->title(
            $formType,
            $kategori,
            $monthName,
            $year
        );

        $section->addText(
            $title,
            [
                'name' => self::FONT_NAME,
                'bold' => true,
                'size' => self::TITLE_FONT_SIZE,
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'lineHeight' => 1.0,
            ]
        );

        /*
         * =========================================================
         * JARAK SEBELUM TINDAK PIDANA
         * =========================================================
         */

        $this->addParagraphSpace(
            $section,
            0.08
        );

        /*
         * =========================================================
         * TINDAK PIDANA
         * =========================================================
         */

        $section->addText(
            ': T.P. ' . ($data['filters']['kategori'] ?? '-'),
            [
                'name' => self::FONT_NAME,
                'bold' => true,
                'size' => self::TITLE_FONT_SIZE,
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'lineHeight' => 1.0,
            ]
        );

        /*
         * =========================================================
         * JARAK SEBELUM TABEL
         * =========================================================
         */

        $this->addParagraphSpace(
            $section,
            0.12
        );

        /*
         * =========================================================
         * TABEL
         * =========================================================
         */

        if ($formType === '3A') {
            $this->addForm3ATable(
                $section,
                $data['cases'] ?? [],
                $tableWidth
            );
        } elseif ($formType === '3C') {
            $this->addForm3CTable(
                $section,
                $data['cases'] ?? [],
                $tableWidth
            );
        }

        /*
         * =========================================================
         * JARAK SEBELUM TANDA TANGAN
         * =========================================================
         */

        $this->addParagraphSpace(
            $section,
            0.25
        );

        /*
         * =========================================================
         * TANDA TANGAN
         * =========================================================
         */

        $this->addSignature(
            $section,
            $tableWidth
        );

        return $phpWord;
    }

    /**
     * Header halaman.
     */
    private function addPageHeader(
        Section $section,
        string $formType,
        int $tableWidth
    ): void {
        $header = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
        ]);

        $row = $header->addRow();

        /*
         * Kolom kiri.
         */
        $left = $row->addCell(
            (int) round($tableWidth * 0.5),
            [
                'borderSize' => 0,
                'valign' => 'top',
                'margin' => 0,
            ]
        );

        $left->addText(
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

        $left->addText(
            'BANDA ACEH',
            [
                'name' => self::FONT_NAME,
                'bold' => true,
                'size' => self::HEADER_FONT_SIZE,
                'underline' => 'single',
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );

        /*
         * Kolom kanan.
         */
        $right = $row->addCell(
            (int) round($tableWidth * 0.5),
            [
                'borderSize' => 0,
                'valign' => 'top',
            ]
        );

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

    /**
     * Judul laporan.
     */
    private function title(
        string $formType,
        string $kategori,
        string $monthName,
        int $year
    ): string {
        if ($formType === '3A') {
            $base = $kategori === 'KORUPSI'
                ? 'LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS'
                : 'LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM';

            return $base
                . "\nBULAN "
                . $monthName
                . ' '
                . $year;
        }

        $base = $kategori === 'KORUPSI'
            ? 'LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS YANG SUDAH MEMPEROLEH'
            : 'LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM YANG SUDAH MEMPEROLEH';

        return $base
            . "\nKEKUATAN HUKUM TETAP DARI PENGADILAN"
            . "\nBULAN "
            . $monthName
            . ' '
            . $year;
    }

    /**
     * =========================================================
     * FORM 3A
     * =========================================================
     */
    private function addForm3ATable(
        Section $section,
        array $cases,
        int $tableWidth
    ): void {
        /*
         * Lebar kolom.
         */
        $widths = [
            3,
            8,
            10,
            9,
            16,
            9,
            11,
            10,
            7,
            9,
            8,
        ];

        $twips = $this->columnWidths(
            $widths,
            $tableWidth
        );

        /*
         * Tabel.
         *
         * tblHeader SENGAJA tidak digunakan.
         * Header tabel tidak boleh diulang di halaman berikutnya.
         */
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 35,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
        ]);

        /*
         * =====================================================
         * STYLE HEADER
         * =====================================================
         */

        $headerStyle = [
            'name' => self::FONT_NAME,
            'bold' => true,
            'size' => self::FORM_3A_TABLE_FONT_SIZE,
        ];

        $headerCellStyle = [
            'borderSize' => 6,
            'valign' => 'center',
            'bgColor' => 'FFFFFF',
        ];

        /*
         * =====================================================
         * BARIS HEADER
         * =====================================================
         */

        $row = $this->addTableRow(
            $table,
            self::FORM_3A_ROW_HEIGHT_CM,
            true
        );

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
            $cell = $row->addCell(
                $twips[$i],
                $headerCellStyle
            );

            /*
             * Header seluruhnya center.
             */
            $this->addCellText(
                $cell,
                $header,
                $headerStyle,
                'center',
                'center'
            );
        }

        /*
         * =====================================================
         * BARIS NOMOR KOLOM
         * =====================================================
         */

        $row = $this->addTableRow(
            $table,
            self::FORM_3A_ROW_HEIGHT_CM,
            true
        );

        for ($i = 1; $i <= 11; $i++) {
            $cell = $row->addCell(
                $twips[$i - 1],
                $headerCellStyle
            );

            $this->addCellText(
                $cell,
                (string) $i,
                $headerStyle,
                'center',
                'center'
            );
        }

        /*
         * =====================================================
         * DATA
         * =====================================================
         */

        if (empty($cases)) {
            $row = $this->addTableRow(
                $table,
                self::FORM_3A_ROW_HEIGHT_CM,
                false
            );

            $cell = $row->addCell(
                $tableWidth,
                $headerCellStyle
            );

            $cell->getStyle()->setGridSpan(11);

            $this->addCellText(
                $cell,
                'NIHIL',
                [
                    'name' => self::FONT_NAME,
                    'bold' => true,
                    'size' => 28,
                ],
                'center',
                'center'
            );

            return;
        }

        foreach ($cases as $idx => $case) {
            $bbList = !empty($case['barangBuktiList'])
                ? $case['barangBuktiList']
                : [null];

            foreach ($bbList as $bIdx => $bb) {
                $row = $this->addTableRow(
                    $table,
                    self::FORM_3A_ROW_HEIGHT_CM,
                    false
                );

                /*
                 * =================================================
                 * MERGE VERTICAL
                 * =================================================
                 */

                $mergeState = $bIdx === 0
                    ? CellStyle::VMERGE_RESTART
                    : CellStyle::VMERGE_CONTINUE;

                /*
                 * No. Urut
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[0],
                    $bIdx === 0
                        ? (string) ($idx + 1)
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Satuan Kerja
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[1],
                    $bIdx === 0
                        ? ($case['satuanKerja'] ?? '-')
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Register Benda Sitaan
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[2],
                    $bIdx === 0
                        ? $this->withDate(
                            $case['noRegBendaSitaan'] ?? '-',
                            $case['tglPenerimaan'] ?? null
                        )
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Register Tahap Penyidikan
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[3],
                    $bIdx === 0
                        ? $this->withDate(
                            $case['noRegPenyidikan'] ?? '-',
                            $case['tglRegPenyidikan'] ?? null
                        )
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Uraian Benda Sitaan
                 * LEFT
                 */
                $uraian = $bb
                    ? trim(
                        ($bb['jumlah'] ?? '-')
                        . ' '
                        . ($bb['satuan'] ?? '-')
                        . ' '
                        . (
                            $bb['uraianBarangBukti']
                            ?? $bb['jenisBarangBukti']
                            ?? '-'
                        )
                    )
                    : '-';

                $this->addPlainCell(
                    $row,
                    $twips[4],
                    $uraian,
                    'left',
                    'top',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Tempat Penyimpanan
                 * CENTER
                 */
                $this->addPlainCell(
                    $row,
                    $twips[5],
                    $bb['tempatPenyimpanan'] ?? '-',
                    'center',
                    'center',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Identitas Tersangka / Terdakwa
                 * LEFT
                 */
                $this->addMergedCell(
                    $row,
                    $twips[6],
                    $bIdx === 0
                        ? ($case['identitasTersangka'] ?? '-')
                        : '',
                    $mergeState,
                    'left',
                    'top',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Pasal
                 * LEFT
                 */
                $this->addMergedCell(
                    $row,
                    $twips[7],
                    $bIdx === 0
                        ? ($case['pasalDisangkakan'] ?? '-')
                        : '',
                    $mergeState,
                    'left',
                    'top',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Diselesaikan
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[8],
                    $bIdx === 0
                        ? ($case['statusDiselesaikan'] ?? '-')
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Tanggal Pelaksanaan Putusan
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[9],
                    $bIdx === 0
                        ? ($case['tglPelaksanaanPutusan'] ?? '-')
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3A_TABLE_FONT_SIZE
                );

                /*
                 * Keterangan
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[10],
                    $bIdx === 0
                        ? ($case['keterangan'] ?? '-')
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3A_TABLE_FONT_SIZE
                );
            }
        }
    }

    /**
     * =========================================================
     * FORM 3C
     * =========================================================
     */
    private function addForm3CTable(
        Section $section,
        array $cases,
        int $tableWidth
    ): void {
        /*
         * Lebar kolom.
         */
        $widths = [
            3,
            7,
            14,
            9,
            8,
            9,
            9,
            9,
            8,
            8,
            8,
            8,
        ];

        $twips = $this->columnWidths(
            $widths,
            $tableWidth
        );

        /*
         * Tabel.
         *
         * tblHeader sengaja tidak digunakan karena header
         * tidak boleh diulang pada halaman berikutnya.
         */
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 30,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
        ]);

        /*
         * =====================================================
         * STYLE HEADER
         * =====================================================
         */

        $headerStyle = [
            'name' => self::FONT_NAME,
            'bold' => true,
            'size' => self::FORM_3C_TABLE_FONT_SIZE,
        ];

        $headerCellStyle = [
            'borderSize' => 6,
            'valign' => 'center',
            'bgColor' => 'FFFFFF',
        ];

        /*
         * =====================================================
         * HEADER
         * =====================================================
         */

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

        $row = $this->addTableRow(
            $table,
            self::FORM_3C_ROW_HEIGHT_CM,
            true
        );

        foreach ($headers as $i => $header) {
            $cell = $row->addCell(
                $twips[$i],
                $headerCellStyle
            );

            /*
             * Header center.
             */
            $this->addCellText(
                $cell,
                $header,
                $headerStyle,
                'center',
                'center'
            );
        }

        /*
         * =====================================================
         * NOMOR KOLOM
         * =====================================================
         */

        $row = $this->addTableRow(
            $table,
            self::FORM_3C_ROW_HEIGHT_CM,
            true
        );

        for ($i = 1; $i <= 12; $i++) {
            $cell = $row->addCell(
                $twips[$i - 1],
                $headerCellStyle
            );

            $this->addCellText(
                $cell,
                (string) $i,
                $headerStyle,
                'center',
                'center'
            );
        }

        /*
         * =====================================================
         * DATA
         * =====================================================
         */

        if (empty($cases)) {
            $row = $this->addTableRow(
                $table,
                self::FORM_3C_ROW_HEIGHT_CM,
                false
            );

            $cell = $row->addCell(
                $tableWidth,
                $headerCellStyle
            );

            $cell->getStyle()->setGridSpan(12);

            $this->addCellText(
                $cell,
                'NIHIL',
                [
                    'name' => self::FONT_NAME,
                    'bold' => true,
                    'size' => 28,
                ],
                'center',
                'center'
            );

            return;
        }

        foreach ($cases as $idx => $case) {
            $bbList = !empty($case['barangBuktiList'])
                ? $case['barangBuktiList']
                : [null];

            foreach ($bbList as $bIdx => $bb) {
                $row = $this->addTableRow(
                    $table,
                    self::FORM_3C_ROW_HEIGHT_CM,
                    false
                );

                $mergeState = $bIdx === 0
                    ? CellStyle::VMERGE_RESTART
                    : CellStyle::VMERGE_CONTINUE;

                /*
                 * No. Urut
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[0],
                    $bIdx === 0
                        ? (string) ($idx + 1)
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Kejaksaan
                 * CENTER
                 */
                $this->addMergedCell(
                    $row,
                    $twips[1],
                    $bIdx === 0
                        ? ($case['satuanKerja'] ?? '-')
                        : '',
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Jenis Barang Bukti
                 * LEFT
                 */
                $jenis = $bb
                    ? '- '
                        . $this->formatJumlah(
                            $bb['jumlah'] ?? null
                        )
                        . ' '
                        . ($bb['jenisBarangBukti'] ?? '-')
                        . ' '
                        . ($bb['uraianBarangBukti'] ?? '')
                    : '-';

                $this->addPlainCell(
                    $row,
                    $twips[2],
                    $jenis,
                    'left',
                    'top',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Pasal Yang Didakwakan
                 * LEFT
                 */
                $this->addMergedCell(
                    $row,
                    $twips[3],
                    $bIdx === 0
                        ? ($case['pasalDidakwakan'] ?? '-')
                        : '',
                    $mergeState,
                    'left',
                    'top',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Register
                 * CENTER
                 */
                $register = $bIdx === 0
                    ? $this->withDate(
                        $case['noRegBendaSitaan'] ?? '-',
                        $case['tglPenerimaan'] ?? null
                    )
                    : '';

                $this->addMergedCell(
                    $row,
                    $twips[4],
                    $register,
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Macam Jenis Kadar
                 * LEFT
                 */
                $this->addPlainCell(
                    $row,
                    $twips[5],
                    '- ' . ($bb['macamJenisKadar'] ?? '-'),
                    'left',
                    'top',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Jumlah Satuan
                 * LEFT
                 */
                $this->addPlainCell(
                    $row,
                    $twips[6],
                    '- ' . $this->formatJumlah(
                        $bb['jumlah'] ?? null
                    ),
                    'left',
                    'top',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Jenis Satuan
                 * LEFT
                 */
                $this->addPlainCell(
                    $row,
                    $twips[7],
                    '- ' . ($bb['satuan'] ?? '-'),
                    'left',
                    'top',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Tempat Penyimpanan
                 * CENTER
                 */
                $this->addPlainCell(
                    $row,
                    $twips[8],
                    $bb['tempatPenyimpanan'] ?? '-',
                    'center',
                    'center',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Tgl & No. KEP
                 * CENTER
                 */
                $kep = $bIdx === 0
                    ? $this->withDate(
                        $case['noKepPengadilan'] ?? '-',
                        $case['tglKepPengadilan'] ?? null
                    )
                    : '';

                $this->addMergedCell(
                    $row,
                    $twips[9],
                    $kep,
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Amar Putusan
                 * LEFT
                 */
                $amar = $this->amarPutusan(
                    $bbList,
                    $bIdx
                );

                $this->addPlainCell(
                    $row,
                    $twips[10],
                    $amar,
                    'left',
                    'top',
                    self::FORM_3C_TABLE_FONT_SIZE
                );

                /*
                 * Tanggal Pelaksanaan Putusan
                 * CENTER
                 */
                $tgl = $bIdx === 0
                    ? $this->formatDate(
                        $case['tglPelaksanaanPutusan'] ?? null
                    )
                    : '';

                $this->addMergedCell(
                    $row,
                    $twips[11],
                    $tgl,
                    $mergeState,
                    'center',
                    'center',
                    self::FORM_3C_TABLE_FONT_SIZE
                );
            }
        }
    }

    /**
     * =========================================================
     * TANDA TANGAN
     * =========================================================
     */
    private function addSignature(
        Section $section,
        int $tableWidth
    ): void {
        /*
         * Ambil setting penanda tangan dari database.
         */
        $setting = Setting::where(
            'key',
            'pejabat_kasi'
        )->first();

        /*
         * Setting::value diasumsikan sudah berupa array
         * melalui cast pada model Setting.
         */
        $pejabatData = $setting?->value ?? [];

        /*
         * Nilai default jika data belum diisi.
         */
        $jabatan = $pejabatData['jabatan_kasi']
            ?? 'KEPALA SEKSI PEMULIHAN ASET DAN PENGELOLAAN BARANG BUKTI';

        $nama = $pejabatData['nama_kasi']
            ?? '-';

        $nip = $pejabatData['nip_kasi']
            ?? '-';

        $pangkat = $pejabatData['pangkat_kasi']
            ?? '';

        /*
         * =====================================================
         * TABEL TTD
         * =====================================================
         */

        $table = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'layout' => TableStyle::LAYOUT_FIXED,
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
        ]);

        $row = $table->addRow();

        /*
         * Ruang kosong kiri 60%.
         */
        $row->addCell(
            (int) round($tableWidth * 0.60),
            [
                'borderSize' => 0,
            ]
        );

        /*
         * TTD kanan 40%.
         */
        $cell = $row->addCell(
            (int) round($tableWidth * 0.40),
            [
                'borderSize' => 0,
                'valign' => 'top',
            ]
        );

        /*
         * =====================================================
         * TANGGAL
         * =====================================================
         */

        $cell->addText(
            'Banda Aceh, '
            . Carbon::now()
                ->locale('id')
                ->translatedFormat('d F Y'),
            [
                'name' => self::FONT_NAME,
                'size' => 10,
                'bold' => false,
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );

        /*
         * =====================================================
         * JABATAN
         * =====================================================
         */

        $cell->addText(
            'Pth. ' . $jabatan,
            [
                'name' => self::FONT_NAME,
                'size' => 10,
                'bold' => true,
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );

        /*
         * =====================================================
         * RUANG TANDA TANGAN
         * =====================================================
         *
         * Tetap diberikan ruang kosong untuk tanda tangan basah.
         */

        $cell->addText(
            '',
            [
                'name' => self::FONT_NAME,
                'size' => 10,
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'lineHeight' => 4.0,
            ]
        );

        /*
         * =====================================================
         * NAMA
         * =====================================================
         */

        $cell->addText(
            $nama,
            [
                'name' => self::FONT_NAME,
                'bold' => true,
                'size' => 10,
                'underline' => 'single',
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );

        /*
         * =====================================================
         * PANGKAT / NIP
         * =====================================================
         */

        $nipText = '';

        if ($pangkat !== '') {
            $nipText .= $pangkat . ' / ';
        }

        $nipText .= 'NIP. ' . $nip;

        $cell->addText(
            $nipText,
            [
                'name' => self::FONT_NAME,
                'bold' => true,
                'size' => 10,
            ],
            [
                'alignment' => 'center',
                'spaceBefore' => 0,
                'spaceAfter' => 0,
            ]
        );
    }

    /**
     * =========================================================
     * HELPER: TABLE ROW
     * =========================================================
     *
     * Menambahkan row dengan tinggi minimum.
     */
    private function addTableRow(
        $table,
        float $heightCm,
        bool $isHeader
    ) {
        /*
         * Tidak menggunakan tblHeader.
         *
         * Ini penting karena user meminta header tabel
         * TIDAK diulang pada halaman berikutnya.
         */
        return $table->addRow(
            Converter::cmToTwip($heightCm),
            [
                'cantSplit' => !$isHeader,
                'exactHeight' => false,
            ]
        );
    }

    /**
     * =========================================================
     * HELPER: COLUMN WIDTH
     * =========================================================
     */
    private function columnWidths(
        array $percentages,
        int $tableWidth
    ): array {
        $total = array_sum($percentages);

        return array_map(
            fn ($percent) => (int) round(
                $tableWidth * ($percent / $total)
            ),
            $percentages
        );
    }

    /**
     * =========================================================
     * HELPER: PLAIN CELL
     * =========================================================
     */
    private function addPlainCell(
        $row,
        int $width,
        string $text,
        string $align,
        string $valign,
        float $fontSize
    ): Cell {
        $cell = $row->addCell(
            $width,
            [
                'borderSize' => 6,
                'valign' => $valign,
            ]
        );

        $this->addCellText(
            $cell,
            $text,
            [
                'name' => self::FONT_NAME,
                'size' => $fontSize,
                'bold' => false,
            ],
            $align,
            $valign
        );

        return $cell;
    }

    /**
     * =========================================================
     * HELPER: MERGED CELL
     * =========================================================
     */
    private function addMergedCell(
        $row,
        int $width,
        string $text,
        string $mergeState,
        string $align,
        string $valign,
        float $fontSize
    ): Cell {
        $cell = $row->addCell(
            $width,
            [
                'borderSize' => 6,
                'vMerge' => $mergeState,
                'valign' => $valign,
            ]
        );

        /*
         * Hanya cell pertama yang berisi teks.
         */
        if ($mergeState === CellStyle::VMERGE_RESTART) {
            $this->addCellText(
                $cell,
                $text,
                [
                    'name' => self::FONT_NAME,
                    'size' => $fontSize,
                    'bold' => false,
                ],
                $align,
                $valign
            );
        }

        return $cell;
    }

    /**
     * =========================================================
     * HELPER: CELL TEXT
     * =========================================================
     */
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

        /*
         * Pecah text berdasarkan newline.
         */
        $parts = preg_split(
            "/\r\n|\r|\n/",
            (string) $text
        );

        foreach ($parts as $index => $part) {
            if ($index > 0) {
                $cell->addTextBreak();
            }

            $cell->addText(
                $part !== '' ? $part : ' ',
                $fontStyle,
                $paragraphStyle
            );
        }
    }

    /**
     * =========================================================
     * HELPER: PARAGRAPH SPACE
     * =========================================================
     *
     * Membuat paragraph kosong dengan tinggi tertentu.
     */
    private function addParagraphSpace(
        Section $section,
        float $heightCm
    ): void {
        $section->addText(
            '',
            [
                'name' => self::FONT_NAME,
                'size' => 1,
            ],
            [
                'spaceBefore' => 0,
                'spaceAfter' => 0,
                'lineHeight' => 1.0,
            ]
        );
    }

    /**
     * =========================================================
     * HELPER: WITH DATE
     * =========================================================
     */
    private function withDate(
        string $number,
        ?string $date
    ): string {
        if (!$date || $date === '-') {
            return $number;
        }

        return $number
            . "\n"
            . $this->formatDate($date);
    }

    /**
     * =========================================================
     * HELPER: FORMAT DATE
     * =========================================================
     */
    private function formatDate(
        ?string $date
    ): string {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return Carbon::parse($date)
                ->locale('id')
                ->translatedFormat('d F Y');
        } catch (\Throwable) {
            return $date;
        }
    }

    /**
     * =========================================================
     * HELPER: FORMAT JUMLAH
     * =========================================================
     */
    private function formatJumlah(
        $value
    ): string {
        if (
            $value === null
            || $value === ''
            || $value === '-'
        ) {
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

        $baca = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas',
        ];

        $terbilang = function ($n) use (
            &$terbilang,
            $baca
        ) {
            if ($n < 12) {
                return $baca[$n];
            }

            if ($n < 20) {
                return $terbilang(
                    $n - 10
                ) . ' belas';
            }

            if ($n < 100) {
                return $terbilang(
                    intdiv($n, 10)
                )
                    . ' puluh '
                    . $terbilang($n % 10);
            }

            if ($n < 200) {
                return 'seratus '
                    . $terbilang(
                        $n - 100
                    );
            }

            if ($n < 1000) {
                return $terbilang(
                    intdiv($n, 100)
                )
                    . ' ratus '
                    . $terbilang($n % 100);
            }

            return (string) $n;
        };

        $word = trim(
            preg_replace(
                '/\s+/',
                ' ',
                $terbilang($angka)
            )
        );

        return $value
            . ' ('
            . $word
            . ')';
    }

    /**
     * =========================================================
     * HELPER: AMAR PUTUSAN
     * =========================================================
     */
    private function amarPutusan(
        array $bbList,
        int $index
    ): string {
        $bb = $bbList[$index] ?? null;

        if (!$bb) {
            return '-';
        }

        $current = trim(
            ($bb['amarPutusan'] ?? '')
            . ' '
            . ($bb['uraianPutusan'] ?? '')
        );

        /*
         * Jika amar putusan sama dengan baris sebelumnya,
         * tampilkan Sda.
         */
        if (
            $index > 0
            && isset($bbList[$index - 1])
        ) {
            $previous = trim(
                ($bbList[$index - 1]['amarPutusan'] ?? '')
                . ' '
                . ($bbList[$index - 1]['uraianPutusan'] ?? '')
            );

            if (
                $previous !== ''
                && $previous === $current
            ) {
                return '- Sda';
            }
        }

        return '- '
            . (
                $current !== ''
                    ? $current
                    : '-'
            );
    }
}