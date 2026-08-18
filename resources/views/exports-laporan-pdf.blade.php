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

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Form {{ $formType }}</title>

    <style>
        /* PAGE SETUP: F4 Landscape dengan Margin 1 cm */
        @page {
            size: 13in 8.5in;
            margin: 0; /* Reset margin kertas bawaan DomPDF */
        }

        html {
            margin: 0;
            padding: 0;
        }

        body {
            /* Margin halaman diatur di sini (Silakan ubah angka 1cm ini sesuai kebutuhan) */
            margin: 1cm; 
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            font-size: 8.5pt;
        }

        /* HEADER HALAMAN & KOP LAPORAN (FLUSH LEFT) */
        .page-header {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 5px;
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
            font-size: 11pt;
            font-weight: bold;
        }

        .kop-box {
            display: inline-block;
            text-align: center;
        }

        .kop-text {
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.1;
        }

        .kop-line {
            border: 0;
            border-top: 1px solid #000000;
            width: 100%;
            margin: 2px 0 0 0;
        }

        /* JUDUL LAPORAN */
        .title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.15;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .tp {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.15;
            margin-top: 2px;
            margin-bottom: 8px;
        }

        /* TABEL LAPORAN UTAMA */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            padding: 0;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #000000 !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: top;
            box-sizing: border-box;
        }

        /* HEADER TABEL FORM 3A */
        .form3a-table .table-header-row th {
            font-size: 8.5pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 4px 2px;
            background-color: #ffffff;
        }

        .form3a-table .table-number-row th {
            font-size: 8pt;
            font-weight: normal;
            text-align: center;
            vertical-align: middle;
            padding: 2px 1px;
            background-color: #ffffff;
        }

        .form3a-table .data-row td {
            font-size: 8.5pt;
            line-height: 1.1;
            padding: 3px 4px;
        }

        .form3a-center {
            text-align: center;
        }

        .form3a-left {
            text-align: left;
        }

        /* HEADER TABEL FORM 3C */
        .form3c-table .table-header-row th {
            font-size: 7.5pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            padding: 3px 2px;
            background-color: #ffffff;
        }

        .form3c-table .table-number-row th {
            font-size: 7pt;
            font-weight: normal;
            text-align: center;
            vertical-align: middle;
            padding: 2px 1px;
            background-color: #ffffff;
        }

        .form3c-table .data-row td {
            font-size: 7.5pt;
            line-height: 1.1;
            padding: 3px 3px;
        }

        .form3c-center {
            text-align: center;
        }

        .form3c-left {
            text-align: left;
        }

        .sub-date {
            font-size: 7.5pt;
            color: #222222;
        }

        /* NIHIL */
        .nihil {
            font-size: 24pt !important;
            font-weight: bold !important;
            text-align: center !important;
            vertical-align: middle !important;
            padding: 25px 0 !important;
        }

        /* TANDA TANGAN (TTD) FORM */
        .ttd-container {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .ttd-container td {
            border: none !important;
            padding: 0 !important;
            vertical-align: top;
        }

        .ttd-left {
            width: 60%;
        }

        .ttd-right {
            width: 40%;
            text-align: center;
        }

        .ttd-box {
            display: inline-block;
            text-align: center;
            font-size: 9.5pt;
            line-height: 1.2;
        }

        .ttd-sign-space {
            height: 1.6cm;
        }

        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd-position {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- KOP & HEADER HALAMAN -->
    <table class="page-header">
        <tr>
            <td class="header-left">
                <div class="kop-box">
                    <div class="kop-text">
                        KEJAKSAAN NEGERI<br>BANDA ACEH
                    </div>
                    <hr class="kop-line">
                </div>
            </td>
            <td class="header-right">
                FORM {{ $formType }}
            </td>
        </tr>
    </table>

    <!-- JUDUL LAPORAN -->
    <div class="title">
        @if($formType === '3A' && strtoupper($kategori) === 'KORUPSI')
            LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS<br>
        @elseif($formType === '3A')
            LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM<br>
        @elseif($formType === '3C' && strtoupper($kategori) === 'KORUPSI')
            LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS YANG SUDAH MEMPEROLEH<br>KEKUATAN HUKUM TETAP DARI PENGADILAN<br>
        @elseif($formType === '3C')
            LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM YANG SUDAH MEMPEROLEH<br>KEKUATAN HUKUM TETAP DARI PENGADILAN<br>
        @endif
        BULAN {{ $monthName }} {{ $year }}
    </div>

    <div class="tp">
        : T.P. {{ $kategori }}
    </div>

    <!-- TABEL FORM 3A -->
    @if($formType === '3A')
        <table class="report-table form3a-table">
            <colgroup>
                <col style="width: 3%;">
                <col style="width: 8%;">
                <col style="width: 10%;">
                <col style="width: 9%;">
                <col style="width: 18%;">
                <col style="width: 9%;">
                <col style="width: 10%;">
                <col style="width: 12%;">
                <col style="width: 6%;">
                <col style="width: 8%;">
                <col style="width: 7%;">
            </colgroup>

            <tbody>
                <!-- HEADER TABEL DIMASUKKAN KE TBODY AGAR TIDAK DIULANG DENGAN PAKSA OLEH DOMPDF -->
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

                @forelse($cases as $idx => $case)
                    @php
                        $bbList = (isset($case['barangBuktiList']) && is_array($case['barangBuktiList']) && count($case['barangBuktiList']) > 0)
                            ? $case['barangBuktiList']
                            : [null];
                    @endphp

                    @foreach($bbList as $bIdx => $bb)
                        <tr class="data-row">
                            <td class="form3a-center">
                                {{ $bIdx === 0 ? ($idx + 1) : '' }}
                            </td>
                            <td class="form3a-center">
                                {{ $bIdx === 0 ? ($case['satuanKerja'] ?? '-') : '' }}
                            </td>
                            <td class="form3a-center">
                                @if($bIdx === 0)
                                    {{ $case['noRegBendaSitaan'] ?? '-' }}
                                    @if(!empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-')
                                        <br><span class="sub-date">{{ formatTanggalLaporan($case['tglPenerimaan']) }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="form3a-center">
                                @if($bIdx === 0)
                                    {{ $case['noRegPenyidikan'] ?? '-' }}
                                    @if(!empty($case['tglRegPenyidikan']) && $case['tglRegPenyidikan'] !== '-')
                                        <br><span class="sub-date">{{ formatTanggalLaporan($case['tglRegPenyidikan']) }}</span>
                                    @endif
                                @endif
                            </td>

                            <td class="form3a-left">
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

                            <td class="form3a-center">
                                @if($bb)
                                    {{ $bb['tempatPenyimpanan'] ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3a-left">
                                {{ $bIdx === 0 ? ($case['identitasTersangka'] ?? '-') : '' }}
                            </td>
                            <td class="form3a-left">
                                {{ $bIdx === 0 ? ($case['pasalDisangkakan'] ?? $case['pasalDidakwakan'] ?? '-') : '' }}
                            </td>
                            <td class="form3a-center">
                                {{ $bIdx === 0 ? ($case['statusDiselesaikan'] ?? '-') : '' }}
                            </td>
                            <td class="form3a-center">
                                {{ $bIdx === 0 ? ($case['tglPelaksanaanPutusan'] ?? '-') : '' }}
                            </td>
                            <td class="form3a-center">
                                {{ $bIdx === 0 ? ($case['keterangan'] ?? '-') : '' }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="11" class="nihil">NIHIL</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    <!-- TABEL FORM 3C -->
    @elseif($formType === '3C')
        <table class="report-table form3c-table">
            <colgroup>
                <col style="width: 3%;">
                <col style="width: 7%;">
                <col style="width: 15%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 7%;">
                <col style="width: 7%;">
                <col style="width: 9%;">
                <col style="width: 9%;">
                <col style="width: 8%;">
                <col style="width: 5%;">
            </colgroup>

            <tbody>
                <!-- HEADER TABEL DIMASUKKAN KE TBODY AGAR TIDAK DIULANG DENGAN PAKSA OLEH DOMPDF -->
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

                @forelse($cases as $idx => $case)
                    @php
                        $bbList = (isset($case['barangBuktiList']) && is_array($case['barangBuktiList']) && count($case['barangBuktiList']) > 0)
                            ? $case['barangBuktiList']
                            : [null];
                    @endphp

                    @foreach($bbList as $bIdx => $bb)
                        <tr class="data-row">
                            <td class="form3c-center">
                                {{ $bIdx === 0 ? ($idx + 1) : '' }}
                            </td>
                            <td class="form3c-center">
                                {{ $bIdx === 0 ? ($case['satuanKerja'] ?? '-') : '' }}
                            </td>

                            <td class="form3c-left">
                                @if($bb)
                                    - {{ formatJumlah($bb['jumlah'] ?? $bb['jumlahSatuan'] ?? null) }} {{ $bb['jenisBarangBukti'] ?? $bb['uraianBarangBukti'] ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3c-left">
                                {{ $bIdx === 0 ? ($case['pasalDidakwakan'] ?? '-') : '' }}
                            </td>

                            <td class="form3c-center">
                                @if($bIdx === 0)
                                    {{ $case['noRegBendaSitaan'] ?? '-' }}
                                    @if(!empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-')
                                        <br><span class="sub-date">{{ formatTanggalLaporan($case['tglPenerimaan']) }}</span>
                                    @endif
                                @endif
                            </td>

                            <td class="form3c-left">
                                @if($bb)
                                    - {{ $bb['macamJenisKadar'] ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3c-left">
                                @if($bb)
                                    - {{ formatJumlah($bb['jumlah'] ?? $bb['jumlahSatuan'] ?? null) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3c-left">
                                @if($bb)
                                    - {{ $bb['satuan'] ?? $bb['jenisSatuan'] ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3c-center">
                                @if($bb)
                                    {{ $bb['tempatPenyimpanan'] ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3c-center">
                                @if($bIdx === 0)
                                    {{ $case['noKepPengadilan'] ?? '-' }}
                                    @if(!empty($case['tglKepPengadilan']) && $case['tglKepPengadilan'] !== '-')
                                        <br><span class="sub-date">{{ formatTanggalLaporan($case['tglKepPengadilan']) }}</span>
                                    @endif
                                @endif
                            </td>

                            <td class="form3c-left">
                                @if($bb)
                                    {{ formatAmarPutusanLaporan($bbList, $bIdx) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="form3c-center">
                                {{ $bIdx === 0 ? formatTanggalLaporan($case['tglPelaksanaanPutusan'] ?? null) : '' }}
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="12" class="nihil">NIHIL</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- BAGIAN TANDA TANGAN -->
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