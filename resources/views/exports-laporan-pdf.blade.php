<?php

/*
|--------------------------------------------------------------------------
| DATA DASAR & TRANSLASI
|--------------------------------------------------------------------------
*/

$monthTranslation = [
    1  => 'JANUARI',
    2  => 'FEBRUARI',
    3  => 'MARET',
    4  => 'APRIL',
    5  => 'MEI',
    6  => 'JUNI',
    7  => 'JULI',
    8  => 'AGUSTUS',
    9  => 'SEPTEMBER',
    10 => 'OKTOBER',
    11 => 'NOVEMBER',
    12 => 'DESEMBER',
];

$formType = strtoupper($filters['formType'] ?? '3A');
$month = (int) ($filters['month'] ?? 1);
$year = (int) ($filters['year'] ?? now()->year);
$kategori = $filters['kategori'] ?? 'ALL';

$monthName = $monthTranslation[$month] ?? '';

/*
|--------------------------------------------------------------------------
| DATA PEJABAT / TANDA TANGAN
|--------------------------------------------------------------------------
*/

try {
    $pejabatSetting = \Illuminate\Support\Facades\DB::table('settings')
        ->where('key', 'pejabat_kasi')
        ->first();

    $pejabatData = $pejabatSetting
        ? json_decode($pejabatSetting->value, true)
        : [];

    if (!is_array($pejabatData)) {
        $pejabatData = [];
    }
} catch (\Throwable $e) {
    $pejabatData = [];
}

$jabatanKasi = $pejabatData['jabatan_kasi']
    ?? 'KEPALA SEKSI PEMULIHAN ASET DAN PENGELOLAAN BARANG BUKTI';

$namaKasi = $pejabatData['nama_kasi'] ?? '-';
$nipKasi = $pejabatData['nip_kasi'] ?? '-';
$pangkatKasi = $pejabatData['pangkat_kasi'] ?? '';

/*
|--------------------------------------------------------------------------
| HELPER TERBILANG
|--------------------------------------------------------------------------
*/

if (!function_exists('terbilang')) {
    function terbilang($angka)
    {
        $angka = (int) $angka;

        if ($angka <= 0) {
            return '';
        }

        $baca = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima',
            'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];

        if ($angka < 12) {
            return $baca[$angka];
        }

        if ($angka < 20) {
            return terbilang($angka - 10) . ' belas';
        }

        if ($angka < 100) {
            return terbilang(floor($angka / 10)) . ' puluh ' . terbilang($angka % 10);
        }

        if ($angka < 200) {
            return 'seratus ' . terbilang($angka - 100);
        }

        if ($angka < 1000) {
            return terbilang(floor($angka / 100)) . ' ratus ' . terbilang($angka % 100);
        }

        return (string) $angka;
    }
}

/*
|--------------------------------------------------------------------------
| HELPER FORMAT JUMLAH
|--------------------------------------------------------------------------
*/

if (!function_exists('formatJumlah')) {
    function formatJumlah($value)
    {
        if ($value === null || $value === '' || $value === '-') {
            return '-';
        }

        if (is_numeric($value)) {
            $teks = trim(preg_replace('/\s+/', ' ', terbilang($value)));
            return $teks ? "{$value} ({$teks})" : (string) $value;
        }

        return (string) $value;
    }
}

/*
|--------------------------------------------------------------------------
| HELPER FORMAT TANGGAL
|--------------------------------------------------------------------------
*/

if (!function_exists('formatTanggalLaporan')) {
    function formatTanggalLaporan($date)
    {
        if (!$date || $date === '-') {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($date)
                ->locale('id')
                ->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }
}

/*
|--------------------------------------------------------------------------
| HELPER AMAR PUTUSAN FORM 3C
|--------------------------------------------------------------------------
*/

if (!function_exists('formatAmarPutusanLaporan')) {
    function formatAmarPutusanLaporan(array $bbList, int $index)
    {
        $bb = $bbList[$index] ?? null;

        if (!$bb) {
            return '-';
        }

        $current = trim(
            ($bb['amarPutusan'] ?? '') . ' ' . ($bb['uraianPutusan'] ?? '')
        );

        if ($index > 0 && isset($bbList[$index - 1])) {
            $previous = trim(
                ($bbList[$index - 1]['amarPutusan'] ?? '') . ' ' . ($bbList[$index - 1]['uraianPutusan'] ?? '')
            );

            if ($previous !== '' && $previous === $current) {
                return '- Sda';
            }
        }

        return '- ' . ($current !== '' ? $current : '-');
    }
}

/*
|--------------------------------------------------------------------------
| HELPER PENGGABUNGAN LOKASI PENYIMPANAN
|--------------------------------------------------------------------------
*/

if (!function_exists('getTempatPenyimpananRowspan')) {
    function getTempatPenyimpananRowspan(array $bbList, int $index) {
        $current = trim($bbList[$index]['tempatPenyimpanan'] ?? '-');
        $count = 1;
        for ($i = $index + 1; $i < count($bbList); $i++) {
            $next = trim($bbList[$i]['tempatPenyimpanan'] ?? '-');
            if ($next === $current) {
                $count++;
            } else {
                break;
            }
        }
        return $count;
    }
}

if (!function_exists('shouldRenderTempatPenyimpanan')) {
    function shouldRenderTempatPenyimpanan(array $bbList, int $index) {
        if ($index === 0) return true;
        $current = trim($bbList[$index]['tempatPenyimpanan'] ?? '-');
        $prev = trim($bbList[$index - 1]['tempatPenyimpanan'] ?? '-');
        return $current !== $prev;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Form {{ $formType }}</title>

    <style>
        /* MARGIN HALAMAN FLSA LANDSCAPE: 1 CM */
        @page {
            margin: 10mm;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
        }

        /* HEADER HALAMAN (KOP & FORM TYPE) - FONT SIZE 12 BOLD */
        .page-header {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
        }

        .page-header td {
            border: none !important;
            padding: 0 !important;
            vertical-align: top;
        }

        .header-left {
            width: 50%;
            text-align: left;
        }

        .header-right {
            width: 50%;
            text-align: right;
            font-size: 12pt;
            font-weight: bold;
        }

        .kop-box {
            display: inline-block;
            text-align: center;
        }

        .kop-text {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.15;
            text-align: center;
        }

        .kop-line {
            border-top: 2px solid #000000;
            margin-top: 2px;
            width: 100%;
        }

        /* SPASI PARAGRAF KUSTOM */
        .spacer-p {
            height: 10px;
            line-height: 10px;
            font-size: 1px;
        }

        /* JUDUL LAPORAN & TINDAK PIDANA - FONT SIZE 14 BOLD */
        .title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .tp {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            line-height: 1.2;
        }

        /* STYLING TABEL UTAMA (SATU TABEL UTUH TERKUNCI) */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
            border: 1px solid #000000;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #000000;
            word-wrap: break-word;
            overflow-wrap: break-word;
            box-sizing: border-box;
            font-weight: normal;
        }

        /* CEGAH PECAH HALAMAN DI TENGAH PERKARA */
        tbody.case-tbody {
            page-break-inside: avoid !important;
        }

        tr {
            page-break-inside: avoid !important;
        }

        /* ATURAN HEADER TABEL */
        .table-header-row th,
        .table-number-row th {
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            background-color: #ffffff;
        }

        /* DETAIL FORM 3A */
        .form3a-table .table-header-row th,
        .form3a-table .table-number-row th {
            font-size: 9pt;
            height: 0.4cm;
            padding: 2px;
        }

        .form3a-table .data-row td {
            font-size: 9pt;
            font-weight: normal;
            line-height: 1.15;
            padding: 4px;
        }

        .form3a-col-center { text-align: center; vertical-align: top; }
        .form3a-col-middle-center { text-align: center; vertical-align: middle; }
        .form3a-col-left { text-align: left; vertical-align: top; }

        /* DETAIL FORM 3B */
        .form3b-table .table-header-row th,
        .form3b-table .table-number-row th {
            font-size: 9pt;
            height: 0.4cm;
            padding: 2px;
        }

        .form3b-table .data-row td {
            font-size: 9pt;
            font-weight: normal;
            line-height: 1.15;
            padding: 4px;
        }

        /* DETAIL FORM 3C */
        .form3c-table .table-header-row th,
        .form3c-table .table-number-row th {
            font-size: 7.5pt;
            height: 0.3cm;
            padding: 2px;
        }

        .form3c-table .data-row td {
            font-size: 7.5pt;
            font-weight: normal;
            line-height: 1.15;
            padding: 3px;
        }

        .form3c-col-center { text-align: center; vertical-align: top; }
        .form3c-col-middle-center { text-align: center; vertical-align: middle; }
        .form3c-col-left { text-align: left; vertical-align: top; }

        .sub-date { font-size: 7.5pt; color: #222222; }

        .nihil {
            font-size: 24pt !important;
            font-weight: bold !important;
            text-align: center !important;
            vertical-align: middle !important;
            padding: 25px 0 !important;
        }

        /* FORMAT TANDA TANGAN (TTD) */
        .ttd-container {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 15px;
            page-break-inside: avoid !important;
        }

        .ttd-container td {
            border: none !important;
            padding: 0 !important;
            vertical-align: top;
        }

        .ttd-left { width: 60%; }
        .ttd-right { width: 40%; text-align: center; }

        .ttd-box {
            display: inline-block;
            text-align: center;
            font-size: 11pt;
            line-height: 1.2;
        }

        .ttd-sign-space { height: 1.8cm; }
        .ttd-name { font-weight: bold; text-decoration: underline; }
        .ttd-position { font-weight: bold; }
    </style>
</head>

<body>

    <!-- KOP & HEADER HALAMAN -->
    <table class="page-header">
        <tr>
            <td class="header-left">
                <div class="kop-box">
                    <div class="kop-text">KEJAKSAAN NEGERI<br>BANDA ACEH</div>
                    <div class="kop-line"></div>
                </div>
            </td>
            <td class="header-right">FORM {{ $formType }}</td>
        </tr>
    </table>

    <!-- SPACE PARAGRAF: KOP KE JUDUL -->
    <div class="spacer-p"></div>

    <!-- JUDUL LAPORAN -->
    <div class="title">
        @if($formType === '3A' && strtoupper($kategori) === 'KORUPSI')
            LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS<br>
        @elseif($formType === '3A')
            LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM<br>
        @elseif($formType === '3B' && strtoupper($kategori) === 'KORUPSI')
            LAPORAN PENYELESAIAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS<br>
        @elseif($formType === '3B')
            LAPORAN PENYELESAIAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM<br>
        @elseif($formType === '3C' && strtoupper($kategori) === 'KORUPSI')
            LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS YANG SUDAH MEMPEROLEH<br>KEKUATAN HUKUM TETAP DARI PENGADILAN<br>
        @elseif($formType === '3C')
            LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM YANG SUDAH MEMPEROLEH<br>KEKUATAN HUKUM TETAP DARI PENGADILAN<br>
        @endif
        BULAN {{ $monthName }} {{ $year }}
    </div>

    <!-- SPACE PARAGRAF: JUDUL KE TINDAK PIDANA -->
    <div class="spacer-p"></div>

    <!-- TINDAK PIDANA -->
    <div class="tp">: T.P. {{ $kategori }}</div>

    <!-- SPACE PARAGRAF: TINDAK PIDANA KE TABEL -->
    <div class="spacer-p"></div>

    <!-- TABEL FORM 3A -->
    @if($formType === '3A')
        <table class="report-table form3a-table">
            <colgroup>
                <col style="width: 3.5%;">
                <col style="width: 8.5%;">
                <col style="width: 10%;">
                <col style="width: 9%;">
                <col style="width: 18%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 13%;">
                <col style="width: 5.5%;">
                <col style="width: 6.5%;">
                <col style="width: 6%;">
            </colgroup>

            <!-- HEADER TABEL UTAMA (TIDAK DIULANG DENGAN MENGGUNAKAN TBODY) -->
            <tbody class="header-tbody">
                <tr class="table-header-row">
                    <th>No. Urut</th>
                    <th>Satuan Kerja</th>
                    <th>Register Benda Sitaan Barang Bukti</th>
                    <th>Register Tahap Penyidikan</th>
                    <th>Uraian Benda Sitaan Jumlah / Satuan / Jenis Barang / Ukuran</th>
                    <th>Tempat Penyimpanan</th>
                    <th>Identitas Tersangka / Terdakwa</th>
                    <th>Pasal yang disangkakan / didakwakan</th>
                    <th>Diselesaikan</th>
                    <th>Tanggal Pelaksanaan Putusan Hakim &amp; Ijin Jaksa Agung</th>
                    <th>Keterangan</th>
                </tr>
                <tr class="table-number-row">
                    <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th>
                    <th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>11</th>
                </tr>
            </tbody>

            <!-- ISIAN PERKARA DIBUNGKUS TBODY TERSENDIRI PER-PERKARA -->
            @forelse($cases as $idx => $case)
                @php
                    $bbList = (isset($case['barangBuktiList']) && is_array($case['barangBuktiList']) && count($case['barangBuktiList']) > 0)
                        ? $case['barangBuktiList']
                        : [null];
                    $totalBb = count($bbList);
                @endphp

                <tbody class="case-tbody">
                    @foreach($bbList as $bIdx => $bb)
                        <tr class="data-row">
                            @if($bIdx === 0)
                                <td class="form3a-col-center" rowspan="{{ $totalBb }}">{{ $idx + 1 }}.</td>
                                <td class="form3a-col-center" rowspan="{{ $totalBb }}">{{ $case['satuanKerja'] ?? '-' }}</td>
                                <td class="form3a-col-center" rowspan="{{ $totalBb }}">
                                    {{ $case['noRegBendaSitaan'] ?? '-' }}
                                    @if(!empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-')
                                        <br><span class="sub-date">{{ formatTanggalLaporan($case['tglPenerimaan']) }}</span>
                                    @endif
                                </td>
                                <td class="form3a-col-center" rowspan="{{ $totalBb }}">
                                    {{ $case['noRegPenyidikan'] ?? '-' }}
                                    @if(!empty($case['tglRegPenyidikan']) && $case['tglRegPenyidikan'] !== '-')
                                        <br><span class="sub-date">{{ formatTanggalLaporan($case['tglRegPenyidikan']) }}</span>
                                    @endif
                                </td>
                            @endif

                            <td class="form3a-col-left">
                                @if($bb)
                                    @php
                                        $satuan = (!empty($bb['satuan']) && trim($bb['satuan']) !== '-') ? trim($bb['satuan']) . ' ' : '';
                                        $uraian = $bb['uraianBarangBukti'] ?? $bb['jenisBarangBukti'] ?? '-';
                                    @endphp
                                    - {{ formatJumlah($bb['jumlah'] ?? null) }} {{ $satuan }}{{ $uraian }}
                                @else
                                    -
                                @endif
                            </td>

                            @if(shouldRenderTempatPenyimpanan($bbList, $bIdx))
                                <td class="form3a-col-middle-center" rowspan="{{ getTempatPenyimpananRowspan($bbList, $bIdx) }}">
                                    {{ $bb ? ($bb['tempatPenyimpanan'] ?? '-') : '-' }}
                                </td>
                            @endif

                            @if($bIdx === 0)
                                <td class="form3a-col-left" rowspan="{{ $totalBb }}">{{ $case['identitasTersangka'] ?? '-' }}</td>
                                <td class="form3a-col-left" rowspan="{{ $totalBb }}">{{ $case['pasalDisangkakan'] ?? $case['pasalDidakwakan'] ?? '-' }}</td>
                                <td class="form3a-col-center" rowspan="{{ $totalBb }}">{{ $case['statusDiselesaikan'] ?? '-' }}</td>
                                <td class="form3a-col-center" rowspan="{{ $totalBb }}">{{ $case['tglPelaksanaanPutusan'] ?? '-' }}</td>
                                <td class="form3a-col-center" rowspan="{{ $totalBb }}">{{ $case['keterangan'] ?? '-' }}</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="11" class="nihil">NIHIL</td>
                    </tr>
                </tbody>
            @endforelse
        </table>

    <!-- TABEL FORM 3B -->
    @elseif($formType === '3B')
        <table class="report-table form3b-table">
            <colgroup>
                <col style="width: 6%;">
                <col style="width: 30%;">
                <col style="width: 13%;">
                <col style="width: 13%;">
                <col style="width: 13%;">
                <col style="width: 12%;">
                <col style="width: 13%;">
            </colgroup>

            <tbody class="header-tbody">
                <tr class="table-header-row">
                    <th rowspan="2" style="vertical-align: middle;">No.<br>Urut</th>
                    <th rowspan="2" style="vertical-align: middle;">Kejaksaan</th>
                    <th colspan="3" style="padding: 2px 0;"></th>
                    <th rowspan="2" style="vertical-align: middle;">Sisa Bulan<br>Laporan</th>
                    <th rowspan="2" style="vertical-align: middle;">Keterangan</th>
                </tr>
                <tr class="table-header-row">
                    <th>Sisa Bulan<br>Lalu</th>
                    <th>Masuk Bulan<br>Laporan</th>
                    <th>Jumlah Bulan<br>Laporan</th>
                </tr>
                <tr class="table-number-row">
                    <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th>
                </tr>

                @forelse($cases as $idx => $case)
                    <tr class="data-row">
                        <td style="text-align: center; vertical-align: top;">{{ $idx + 1 }}.</td>
                        <td style="text-align: left; vertical-align: top;">{{ $case['satuanKerja'] ?? 'Kejari Banda Aceh' }}</td>
                        <td style="text-align: center; vertical-align: top;">{{ $case['sisaBulanLalu'] ?? 0 }}</td>
                        <td style="text-align: center; vertical-align: top;">{{ $case['masukBulanLaporan'] ?? 0 }}</td>
                        <td style="text-align: center; vertical-align: top; font-weight: bold;">{{ $case['jumlahBulanLaporan'] ?? 0 }}</td>
                        <td style="text-align: center; vertical-align: top;">{{ $case['sisaBulanLaporan'] ?? 0 }}</td>
                        <td style="text-align: center; vertical-align: top;">{{ $case['keterangan'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="nihil">NIHIL</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    <!-- TABEL FORM 3C -->
    @elseif($formType === '3C')
        <table class="report-table form3c-table">
            <colgroup>
                <col style="width: 3%;">
                <col style="width: 7.5%;">
                <col style="width: 18%;">
                <col style="width: 10%;">
                <col style="width: 9%;">
                <col style="width: 7%;">
                <col style="width: 6%;">
                <col style="width: 5.5%;">
                <col style="width: 8.5%;">
                <col style="width: 8%;">
                <col style="width: 11.5%;">
                <col style="width: 6%;">
            </colgroup>

            <!-- HEADER TABEL UTAMA (TIDAK DIULANG DENGAN MENGGUNAKAN TBODY) -->
            <tbody class="header-tbody">
                <tr class="table-header-row">
                    <th>No. Urut</th>
                    <th>Kejaksaan</th>
                    <th>Jenis Barang Bukti</th>
                    <th>Pasal Yang Didakwakan</th>
                    <th>Register Benda Sitaan Barang Bukti / Tanggal Penerimaan Barang Bukti</th>
                    <th>Macam Jenis Kadar</th>
                    <th>Jumlah Satuan</th>
                    <th>Jenis Satuan</th>
                    <th>Tempat Penyimpanan</th>
                    <th>Tgl &amp; No. KEP PN/PT/MA</th>
                    <th>Amar Putusan</th>
                    <th>Tanggal Pelaksanaan Putusan Hakim</th>
                </tr>
                <tr class="table-number-row">
                    <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th>
                    <th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th>
                </tr>
            </tbody>

            <!-- ISIAN PERKARA DIBUNGKUS TBODY TERSENDIRI PER-PERKARA -->
            @forelse($cases as $idx => $case)
                @php
                    $bbList = (isset($case['barangBuktiList']) && is_array($case['barangBuktiList']) && count($case['barangBuktiList']) > 0)
                        ? $case['barangBuktiList']
                        : [null];
                    $totalBb = count($bbList);
                @endphp

                <tbody class="case-tbody">
                    @foreach($bbList as $bIdx => $bb)
                        <tr class="data-row">
                            @if($bIdx === 0)
                                <td class="form3c-col-center" rowspan="{{ $totalBb }}">{{ $idx + 1 }}.</td>
                                <td class="form3c-col-center" rowspan="{{ $totalBb }}">{{ $case['satuanKerja'] ?? '-' }}</td>
                            @endif

                            <td class="form3c-col-left">
                                @if($bb)
                                    @php
                                        $uraian = $bb['jenisBarangBukti'] ?? $bb['uraianBarangBukti'] ?? '-';
                                    @endphp
                                    - {{ $uraian }}
                                @else
                                    -
                                @endif
                            </td>

                            @if($bIdx === 0)
                                <td class="form3c-col-left" rowspan="{{ $totalBb }}">{{ $case['pasalDidakwakan'] ?? '-' }}</td>
                                <td class="form3c-col-center" rowspan="{{ $totalBb }}">
                                    {{ $case['noRegBendaSitaan'] ?? '-' }}
                                    @if(!empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-')
                                        <br><span class="sub-date">{{ formatTanggalLaporan($case['tglPenerimaan']) }}</span>
                                    @endif
                                </td>
                            @endif

                            <td class="form3c-col-left">
                                @if($bb && !empty($bb['macamJenisKadar']) && $bb['macamJenisKadar'] !== '-')
                                    - {{ $bb['macamJenisKadar'] }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3c-col-left">
                                @if($bb)
                                    - {{ formatJumlah($bb['jumlah'] ?? $bb['jumlahSatuan'] ?? null) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3c-col-left">
                                @if($bb && (!empty($bb['satuan']) || !empty($bb['jenisSatuan'])))
                                    - {{ $bb['satuan'] ?? $bb['jenisSatuan'] }}
                                @else
                                    -
                                @endif
                            </td>

                            @if(shouldRenderTempatPenyimpanan($bbList, $bIdx))
                                <td class="form3c-col-middle-center" rowspan="{{ getTempatPenyimpananRowspan($bbList, $bIdx) }}">
                                    {{ $bb ? ($bb['tempatPenyimpanan'] ?? '-') : '-' }}
                                </td>
                            @endif

                            @if($bIdx === 0)
                                <td class="form3c-col-center" rowspan="{{ $totalBb }}">
                                    {{ $case['noKepPengadilan'] ?? '-' }}
                                    @if(!empty($case['tglKepPengadilan']) && $case['tglKepPengadilan'] !== '-')
                                        <br><span class="sub-date">{{ formatTanggalLaporan($case['tglKepPengadilan']) }}</span>
                                    @endif
                                </td>
                            @endif

                            <td class="form3c-col-left">
                                @if($bb)
                                    {{ formatAmarPutusanLaporan($bbList, $bIdx) }}
                                @else
                                    -
                                @endif
                            </td>

                            @if($bIdx === 0)
                                <td class="form3c-col-center" rowspan="{{ $totalBb }}">
                                    {{ formatTanggalLaporan($case['tglPelaksanaanPutusan'] ?? null) }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="12" class="nihil">NIHIL</td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    @endif

    <!-- SPACE PARAGRAF: SEBELUM TTD -->
    <div class="spacer-p" style="height: 15px;"></div>

    <!-- FORMAT TANDA TANGAN (TTD) -->
    <table class="ttd-container">
        <tr>
            <td class="ttd-left">&nbsp;</td>
            <td class="ttd-right">
                <div class="ttd-box">
                    Banda Aceh, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                    <span class="ttd-position">
                        Pth. {!! str_replace(' DAN ', '<br>DAN ', e($jabatanKasi)) !!}
                    </span>
                    <div class="ttd-sign-space">&nbsp;</div>
                    <span class="ttd-name">{{ $namaKasi }}</span><br>
                    <span class="ttd-position">
                        {{ $pangkatKasi ? $pangkatKasi . ' / ' : '' }}NIP. {{ $nipKasi }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>