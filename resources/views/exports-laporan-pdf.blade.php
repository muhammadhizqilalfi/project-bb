<?php
$monthTranslation = [
    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
    5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
    9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
];

$monthName = $monthTranslation[(int)($filters['month'] ?? 1)] ?? '';

try {
    $pejabatSetting = \Illuminate\Support\Facades\DB::table('settings')
        ->where('key', 'pejabat_kasi')
        ->first();
    $pejabatData = $pejabatSetting ? json_decode($pejabatSetting->value, true) : [];
} catch (\Throwable $e) {
    $pejabatData = [];
}

$jabatanKasi = $pejabatData['jabatan_kasi'] ?? 'KEPALA SEKSI PEMULIHAN ASET DAN PENGELOLAAN BARANG BUKTI';
$namaKasi = $pejabatData['nama_kasi'] ?? 'JOHN DOE';
$nipKasi = $pejabatData['nip_kasi'] ?? '-';
$pangkatKasi = $pejabatData['pangkat_kasi'] ?? '';

if (!function_exists('laporan_pdf_format_jumlah')) {
    function laporan_pdf_format_jumlah($val)
    {
        if ($val === null || $val === '' || $val === '-') return '-';
        if (!is_numeric($val)) return (string) $val;

        $angka = (int) $val;
        if ($angka < 0) return (string) $val;
        if ($angka === 0) return '0';

        $baca = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $terbilang = function ($n) use (&$terbilang, $baca) {
            if ($n < 12) return $baca[$n];
            if ($n < 20) return $terbilang($n - 10) . ' belas';
            if ($n < 100) return $terbilang(intdiv($n, 10)) . ' puluh ' . $terbilang($n % 10);
            if ($n < 200) return 'seratus ' . $terbilang($n - 100);
            if ($n < 1000) return $terbilang(intdiv($n, 100)) . ' ratus ' . $terbilang($n % 100);
            return (string) $n;
        };

        return $val . ' (' . trim(preg_replace('/\s+/', ' ', $terbilang($angka))) . ')';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Form {{ $filters['formType'] }}</title>
<style>
    @page {
        size: 13in 8.5in;
        margin: 1cm;
    }

    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #000;
        font-size: 9pt;
    }

    .report {
        width: 100%;
    }

    .kop-table,
    .ttd-container {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .kop-table td,
    .ttd-container td {
        border: none;
        padding: 0;
    }

    .kop-left {
        width: 50%;
        text-align: center;
        font-size: 12pt;
        font-weight: bold;
    }

    .kop-left span {
        display: inline-block;
        border-bottom: 1.5pt solid #000;
        padding-bottom: 2px;
    }

    .kop-right {
        width: 50%;
        text-align: right;
        vertical-align: top;
        font-size: 12pt;
        font-weight: bold;
    }

    .title {
        margin: 10pt 0 3pt;
        text-align: center;
        font-size: 14pt;
        font-weight: bold;
        line-height: 1.2;
    }

    .tp {
        margin: 0 0 10pt;
        text-align: center;
        font-size: 14pt;
        font-weight: bold;
    }

    table.data-table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        table-layout: fixed;
        page-break-inside: auto;
    }

    table.data-table thead {
        display: table-header-group;
    }

    table.data-table tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }

    table.data-table th,
    table.data-table td {
        border: 1px solid #000;
        padding: 2.5px;
        vertical-align: top;
        color: #000;
        white-space: normal;
        overflow-wrap: break-word;
        word-wrap: break-word;
        word-break: normal;
        line-height: 1.05;
    }

    table.form-3a-font th,
    table.form-3a-font td {
        font-size: 8.3pt;
    }

    table.form-3c-font th,
    table.form-3c-font td {
        font-size: 7pt;
    }

    .header-row th {
        text-align: center;
        vertical-align: middle !important;
        font-weight: bold;
    }

    .number-row th {
        height: 12pt;
        padding-top: 1px;
        padding-bottom: 1px;
    }

    .nihil {
        text-align: center;
        font-size: 28pt !important;
        font-weight: bold;
        padding: 12pt 0 !important;
    }

    .ttd-container {
        margin-top: 14pt;
        page-break-inside: avoid;
    }

    .ttd-spacer {
        width: 60%;
    }

    .ttd {
        width: 40%;
        text-align: center;
        vertical-align: top;
        font-size: 10pt;
    }
</style>
</head>
<body>
<div class="report">

    <table class="kop-table">
        <tr>
            <td class="kop-left">
                <span>KEJAKSAAN NEGERI<br>BANDA ACEH</span>
            </td>
            <td class="kop-right">FORM {{ $filters['formType'] }}</td>
        </tr>
    </table>

    <div class="title">
        @if(($filters['formType'] ?? '') === '3A' && ($filters['kategori'] ?? '') === 'KORUPSI')
            LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS<br>
        @elseif(($filters['formType'] ?? '') === '3A')
            LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM<br>
        @elseif(($filters['formType'] ?? '') === '3C' && ($filters['kategori'] ?? '') === 'KORUPSI')
            LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS YANG SUDAH MEMPEROLEH<br>
            KEKUATAN HUKUM TETAP DARI PENGADILAN<br>
        @elseif(($filters['formType'] ?? '') === '3C')
            LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM YANG SUDAH MEMPEROLEH<br>
            KEKUATAN HUKUM TETAP DARI PENGADILAN<br>
        @endif
        BULAN {{ $monthName }} {{ $filters['year'] }}
    </div>

    <div class="tp">: T.P. {{ $filters['kategori'] }}</div>

    @if(($filters['formType'] ?? '') === '3A')
        <table class="data-table form-3a-font">
            <colgroup>
                <col style="width:3%">
                <col style="width:8%">
                <col style="width:10%">
                <col style="width:9%">
                <col style="width:16%">
                <col style="width:9%">
                <col style="width:11%">
                <col style="width:10%">
                <col style="width:7%">
                <col style="width:9%">
                <col style="width:8%">
            </colgroup>
            <thead>
                <tr class="header-row">
                    <th>No. Urut</th>
                    <th>Satuan Kerja</th>
                    <th>Register Benda Sitaan Barang Bukti</th>
                    <th>Register Tahap Penyidikan</th>
                    <th>Uraian Benda Sitaan Jumlah / Satuan / Jenis / Barang / Ukuran</th>
                    <th>Tempat Penyimpanan</th>
                    <th>Identitas Tersangka / Terdakwa</th>
                    <th>Pasal yang disangkakan / didakwakan</th>
                    <th>Diselesaikan</th>
                    <th>Tanggal Pelaksanaan Putusan Hakim &amp; Ijin Jaksa Agung</th>
                    <th>Keterangan</th>
                </tr>
                <tr class="header-row number-row">
                    @for($i = 1; $i <= 11; $i++)
                        <th>{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
            @forelse($cases as $idx => $case)
                @php
                    $bbList = !empty($case['barangBuktiList']) ? $case['barangBuktiList'] : [null];
                    $rowSpan = count($bbList);
                @endphp

                @foreach($bbList as $bIdx => $bb)
                    <tr>
                        @if($bIdx === 0)
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">{{ $idx + 1 }}</td>
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">{{ $case['satuanKerja'] ?? '-' }}</td>
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">
                                {{ $case['noRegBendaSitaan'] ?? '-' }}<br>
                                @if(!empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-')
                                    {{ \Carbon\Carbon::parse($case['tglPenerimaan'])->locale('id')->translatedFormat('d F Y') }}
                                @endif
                            </td>
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">
                                {{ $case['noRegPenyidikan'] ?? '-' }}<br>
                                @if(!empty($case['tglRegPenyidikan']) && $case['tglRegPenyidikan'] !== '-')
                                    {{ \Carbon\Carbon::parse($case['tglRegPenyidikan'])->locale('id')->translatedFormat('d F Y') }}
                                @endif
                            </td>
                        @endif

                        <td>
                            @if($bb)
                                {{ $bb['jumlah'] ?? '-' }} {{ $bb['satuan'] ?? '-' }}
                                {{ $bb['uraianBarangBukti'] ?? $bb['jenisBarangBukti'] ?? '-' }}
                            @else
                                -
                            @endif
                        </td>

                        <td style="text-align:center;">{{ $bb['tempatPenyimpanan'] ?? '-' }}</td>

                        @if($bIdx === 0)
                            <td rowspan="{{ $rowSpan }}">{{ $case['identitasTersangka'] ?? '-' }}</td>
                            <td rowspan="{{ $rowSpan }}">{{ $case['pasalDisangkakan'] ?? '-' }}</td>
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">{{ $case['statusDiselesaikan'] ?? '-' }}</td>
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">{{ $case['tglPelaksanaanPutusan'] ?? '-' }}</td>
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">{{ $case['keterangan'] ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="11" class="nihil">NIHIL</td>
                </tr>
            @endforelse
            </tbody>
        </table>

    @elseif(($filters['formType'] ?? '') === '3C')
        <table class="data-table form-3c-font">
            <colgroup>
                <col style="width:3%">
                <col style="width:7%">
                <col style="width:14%">
                <col style="width:9%">
                <col style="width:8%">
                <col style="width:9%">
                <col style="width:9%">
                <col style="width:9%">
                <col style="width:8%">
                <col style="width:8%">
                <col style="width:8%">
                <col style="width:8%">
            </colgroup>
            <thead>
                <tr class="header-row">
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
                <tr class="header-row number-row">
                    @for($i = 1; $i <= 12; $i++)
                        <th>{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
            @forelse($cases as $idx => $case)
                @php
                    $bbList = !empty($case['barangBuktiList']) ? $case['barangBuktiList'] : [null];
                    $rowSpan = count($bbList);
                @endphp

                @foreach($bbList as $bIdx => $bb)
                    <tr>
                        @if($bIdx === 0)
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">{{ $idx + 1 }}</td>
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">{{ $case['satuanKerja'] ?? '-' }}</td>
                        @endif

                        <td>
                            @if($bb)
                                - {{ laporan_pdf_format_jumlah($bb['jumlah'] ?? null) }}
                                {{ $bb['jenisBarangBukti'] ?? '-' }}
                                {{ $bb['uraianBarangBukti'] ?? '' }}
                            @else
                                -
                            @endif
                        </td>

                        @if($bIdx === 0)
                            <td rowspan="{{ $rowSpan }}">{{ $case['pasalDidakwakan'] ?? '-' }}</td>
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">
                                {{ $case['noRegBendaSitaan'] ?? '-' }}<br>
                                @if(!empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-')
                                    {{ \Carbon\Carbon::parse($case['tglPenerimaan'])->locale('id')->translatedFormat('d F Y') }}
                                @endif
                            </td>
                        @endif

                        <td>- {{ $bb['macamJenisKadar'] ?? '-' }}</td>
                        <td>- {{ laporan_pdf_format_jumlah($bb['jumlah'] ?? null) }}</td>
                        <td>- {{ $bb['satuan'] ?? '-' }}</td>
                        <td style="text-align:center;">{{ $bb['tempatPenyimpanan'] ?? '-' }}</td>

                        @if($bIdx === 0)
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">
                                {{ $case['noKepPengadilan'] ?? '-' }}<br>
                                @if(!empty($case['tglKepPengadilan']) && $case['tglKepPengadilan'] !== '-')
                                    {{ \Carbon\Carbon::parse($case['tglKepPengadilan'])->locale('id')->translatedFormat('d F Y') }}
                                @endif
                            </td>
                        @endif

                        <td>
                            @if($bb)
                                @php
                                    $isSdaAmar = false;
                                    if ($bIdx > 0 && isset($bbList[$bIdx - 1])) {
                                        $prevAmar = trim(($bbList[$bIdx - 1]['amarPutusan'] ?? '') . ' ' . ($bbList[$bIdx - 1]['uraianPutusan'] ?? ''));
                                        $currAmar = trim(($bb['amarPutusan'] ?? '') . ' ' . ($bb['uraianPutusan'] ?? ''));
                                        $isSdaAmar = $prevAmar !== '' && $prevAmar === $currAmar;
                                    }
                                @endphp

                                @if($isSdaAmar)
                                    - Sda
                                @else
                                    - {{ $bb['amarPutusan'] ?? '' }} {{ $bb['uraianPutusan'] ?? '' }}
                                @endif
                            @else
                                -
                            @endif
                        </td>

                        @if($bIdx === 0)
                            <td rowspan="{{ $rowSpan }}" style="text-align:center;">
                                @if(!empty($case['tglPelaksanaanPutusan']) && $case['tglPelaksanaanPutusan'] !== '-')
                                    {{ \Carbon\Carbon::parse($case['tglPelaksanaanPutusan'])->locale('id')->translatedFormat('d F Y') }}
                                @else
                                    {{ $case['tglPelaksanaanPutusan'] ?? '-' }}
                                @endif
                            </td>
                        @endif
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

    <table class="ttd-container">
        <tr>
            <td class="ttd-spacer"></td>
            <td class="ttd">
                Banda Aceh, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                <b>Pth. {{ $jabatanKasi }}</b>
                <br><br><br><br>
                <b><u>{{ $namaKasi }}</u></b><br>
                <b>{{ $pangkatKasi ? $pangkatKasi . ' / ' : '' }}NIP. {{ $nipKasi }}</b>
            </td>
        </tr>
    </table>

</div>
</body>
</html>
