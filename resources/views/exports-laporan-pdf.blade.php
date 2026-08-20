<?php
    $monthTranslation = [
        1 => 'JANUARI',   2 => 'FEBRUARI', 3 => 'MARET',     4 => 'APRIL',
        5 => 'MEI',       6 => 'JUNI',     7 => 'JULI',      8 => 'AGUSTUS',
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

    // Menggunakan null-coalescing operator agar safe di PHP 8+
    $jabatanKasi = $pejabatData['jabatan_kasi'] ?? '-';
    $namaKasi    = $pejabatData['nama_kasi'] ?? '-';
    $nipKasi     = $pejabatData['nip_kasi'] ?? '-';
    $pangkatKasi = $pejabatData['pangkat_kasi'] ?? '';

    if (!function_exists('terbilang')) {
        function terbilang($angka) {
            $angka = (int)$angka;
            if ($angka <= 0) return '';
            $baca = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

            if ($angka < 12) {
                return $baca[$angka];
            } elseif ($angka < 20) {
                return terbilang($angka - 10) . ' belas';
            } elseif ($angka < 100) {
                return terbilang(floor($angka / 10)) . ' puluh ' . terbilang($angka % 10);
            } elseif ($angka < 200) {
                return 'seratus ' . terbilang($angka - 100);
            } elseif ($angka < 1000) {
                return terbilang(floor($angka / 100)) . ' ratus ' . terbilang($angka % 100);
            }
            return (string)$angka;
        }
    }

    if (!function_exists('formatJumlah')) {
        function formatJumlah($val) {
            if (empty($val) || $val === '-') return '-';

            if (is_numeric($val)) {
                $teks = trim(preg_replace('/\s+/', ' ', terbilang($val)));
                return $teks ? "{$val} ({$teks})" : $val;
            }

            return $val;
        }
    }
?>

<!DOCTYPE html>
<html xmlns:o='urn:schemas-microsoft-com:office:office'
      xmlns:w='urn:schemas-microsoft-com:office:word'
>
<head>
    <meta charset="utf-8">
    <title>Laporan Form {{ $filters['formType'] }}</title>
    <style>
        @page Section1 {
            mso-page-orientation: landscape;
            mso-header: h1;
        }

        div.Section1 {
            page: Section1;
        }

        body { font-family: Arial, sans-serif; font-size: 12pt; }
        .title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px; text-transform: uppercase; }

        /* Layout tabel diperketat untuk Dompdf */
        table { border-collapse: collapse; table-layout: fixed; width: 100%; margin-top: 10px; }
        th, td { 
            border: 1px solid #000000; 
            padding: 5px; 
            text-align: center; 
            font-size: 9pt; 
            white-space: normal !important; 
            word-wrap: break-word !important; 
            overflow-wrap: break-word; 
            vertical-align: top;
        }
        th { font-weight: bold; }
        tr { page-break-inside: avoid !important; }

        .text-top { vertical-align: top; }
        .text-left { text-align: left; }
        .tp { text-align: center; font-weight: bold; margin-bottom: 1rem; font-size: 14pt; }
        .satker { font-weight: bold; }

        /* Fix CSS typo: margin-top */
        .header-container { width: 100%; border-collapse: collapse; margin-bottom: 20px; margin-top: -1.5rem; }
        .header-container td { border: none !important; padding: 0 !important; vertical-align: top; }
        .header1 { width: 50%;}
        .header2 { font-weight: bold; font-size: 12pt; text-align: right; width: 50%; }
        .kop-kiri { border-collapse: collapse; margin-left: -10rem; }
        .kop-kiri td { border: none !important; padding: 0 !important; text-align: center; }
        .kop-text { font-weight: bold; font-size: 12pt; text-align: center; white-space: nowrap; }
        .line-header { border: none; border-top: 1px solid #000; margin-top: 4px; margin-bottom: 0; width: 40%;}
        .noth * { font-weight: normal; font-size: 9pt; padding: 0 4px 0 0; }

        .ttd-container {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .ttd-container td {
            border: none !important;
            padding: 0 !important;
            vertical-align: top;
            text-align: center;
            font-size: 13pt;
        }

        .ttd-space { height: 65px; }

        .nihil { 
            font-size: 40pt; 
            font-weight: 900;
            font-family: Arial;
            color: red;
            text-align: center;
            letter-spacing: 12px;
            padding: 15px 0;
            -webkit-text-stroke: 1.8px black; 
            text-shadow: 
                -1.5px -1.5px 0 #000,
                1.5px -1.5px 0 #000,
                -1.5px  1.5px 0 #000,
                1.5px  1.5px 0 #000;
        }
    </style>
</head>
<body>
    <div class="Section1">
        <table class="header-container">
            <tr>
                <td class="header1">
                    <table class="kop-kiri">
                        <tr>
                            <td class="kop-text">
                                KEJAKSAAN NEGERI<br>
                                BANDA ACEH
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <hr class="line-header">
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="header2">
                    FORM {{ $filters['formType'] }}
                </td>
            </tr>
        </table>

        <div class="title">
            @if((($filters['formType']) === '3A') && ($filters['kategori']) === 'KORUPSI')
                LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS<br>
            @elseif(($filters['formType']) === '3A')
                LAPORAN BENDA SITAAN DAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM<br>
            @elseif((($filters['formType']) === '3C') && ($filters['kategori']) === 'KORUPSI')
                LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA KHUSUS YANG SUDAH MEMPEROLEH<br>
                KEKUATAN HUKUM TETAP DARI PENGADILAN<br>
            @elseif(($filters['formType']) === '3C')
                LAPORAN BARANG BUKTI PERKARA TINDAK PIDANA UMUM YANG SUDAH MEMPEROLEH<br>
                KEKUATAN HUKUM TETAP DARI PENGADILAN<br>
            @endif
            BULAN {{ $monthName }} {{ $filters['year'] }}
        </div>

        <div class="tp">
            : T.P. {{ $filters['kategori'] }} 
        </div>

        @if(($filters['formType']) === '3A')
            <table>
                <tbody>
                    {{-- HEADER TABEL (Tanpa tag <thead> agar TIDAK diulang Dompdf di Halaman 2 dst) --}}
                    <tr>
                        <th width="3%">No. Urut</th>
                        <th width="8%">Satuan Kerja</th>
                        <th width="10%">Register Benda Sitaan Barang Bukti</th>
                        <th width="10%">Register Tahap Penyidikan</th>
                        <th width="15%">Uraian Benda Sitaan Jumlah / Satuan/ Jenis / Barang / Ukuran</th>
                        <th width="8%">Tempat Penyimpanan</th>
                        <th width="11%">Identitas Tersangka / Terdakwa</th>
                        <th width="20%">Pasal yang disangkakan / didakwakan</th>
                        <th width="6%">Diselesaikan</th>
                        <th width="6%">Tanggal Pelaksanaan Putusan Hakim & Ijin Jaksa Agung</th>
                        <th width="10%">Keterangan</th>
                    </tr>
                    <tr class="noth">
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                        <th>7</th>
                        <th>8</th>
                        <th>9</th>
                        <th>10</th>
                        <th>11</th>
                    </tr>

                    @forelse($cases as $idx => $case)
                        @php 
                            $bbRaw = $case['barangBuktiList'] ?? [];
                            $bbList = (is_array($bbRaw) && count($bbRaw) > 0) ? array_values($bbRaw) : []; 

                            // 1. Cek semua tempat penyimpanan unik di perkara ini
                            $tpList = array_values(array_filter(array_map(function($item) {
                                return trim($item['tempatPenyimpanan'] ?? $item['tempat_penyimpanan'] ?? '');
                            }, $bbList)));

                            $uniqueTp = array_values(array_unique($tpList));
                            $isSingleTp = count($uniqueTp) <= 1;
                            $singleTpText = count($uniqueTp) === 1 ? $uniqueTp[0] : '-';

                            // 2. Grouping tempat penyimpanan berurutan jika ada beberapa tempat berbeda
                            $tpGroups = [];
                            foreach ($bbList as $bIdx => $bb) {
                                $tpName = trim($bb['tempatPenyimpanan'] ?? $bb['tempat_penyimpanan'] ?? '') ?: '-';
                                if (empty($tpGroups) || $tpGroups[count($tpGroups) - 1]['name'] !== $tpName) {
                                    $tpGroups[] = [
                                        'name' => $tpName,
                                        'count' => 1,
                                    ];
                                } else {
                                    $tpGroups[count($tpGroups) - 1]['count']++;
                                }
                            }
                        @endphp

                        <tr style="page-break-inside: avoid !important;">
                            {{-- 1. No Urut --}}
                            <td class="text-center">{{ $idx + 1 }}</td>

                            {{-- 2. Satker --}}
                            <td class="satker wrap-text" style="vertical-align: middle;">{{ $case['satuanKerja'] ?? '-' }}</td>

                            {{-- 3. Register Benda Sitaan --}}
                            <td class="wrap-text">
                                {{ $case['noRegBendaSitaan'] ?? '-' }}<br>
                                <small>
                                    {{ !empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-' ? \Carbon\Carbon::parse($case['tglPenerimaan'])->locale('id')->translatedFormat('d F Y') : '-' }}
                                </small>
                            </td>

                            {{-- 4. Register Penyidikan --}}
                            <td class="wrap-text">
                                {{ $case['noRegPenyidikan'] ?? '-' }}<br>
                                <small>
                                    {{ !empty($case['tglRegPenyidikan']) && $case['tglRegPenyidikan'] !== '-' ? \Carbon\Carbon::parse($case['tglRegPenyidikan'])->locale('id')->translatedFormat('d F Y') : '-' }}
                                </small>
                            </td>

                            {{-- 5. Uraian Barang Bukti --}}
                            <td class="text-left wrap-text">
                                @forelse($bbList as $bIdx => $bb)
                                    <div style="{{ !$loop->last ? 'margin-bottom: 100px; padding-bottom: 100px;' : '' }}">
                                        - {{ formatJumlah($bb['jumlah'] ?? null) }} {{ $bb['uraianBarangBukti'] ?? $bb['jenisBarangBukti'] ?? '' }}
                                    </div>
                                @empty
                                    -
                                @endforelse
                            </td>

                            {{-- 6. Tempat Penyimpanan --}}
                            <td class="wrap-text text-center" style="vertical-align: middle;" >
                                @if($isSingleTp)
                                    {{ $singleTpText }}
                                @else
                                    @foreach($tpGroups as $gIdx => $group)
                                        <div style="{{ !$loop->last ? 'margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px dashed #aaa;' : '' }} padding-top: 2px;">
                                            {{ $group['name'] }}
                                        </div>
                                    @endforeach
                                @endif
                            </td>

                            {{-- 7. Tersangka --}}
                            <td class="text-left wrap-text">- {{ $case['identitasTersangka'] ?? '-' }}</td>

                            {{-- 8. Pasal --}}
                            <td class="text-left wrap-text">{{ $case['pasalDisangkakan'] ?? '-' }}</td>

                            {{-- 9. Diselesaikan --}}
                            <td class="text-center">{{ $case['statusDiselesaikan'] ?? '-' }}</td>

                            {{-- 10. Tgl Putusan --}}
                            <td class="text-center">{{ $case['tglPelaksanaanPutusan'] ?? '-' }}</td>

                            {{-- 11. Keterangan --}}
                            <td class="wrap-text">{{ $case['keterangan'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="nihil">NIHIL</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @elseif(($filters['formType']) === '3C')
            <table>
                <tbody>
                    {{-- HEADER TABEL FORM 3C --}}
                    <tr>
                        <th width="3%">No. Urut</th>
                        <th>Kejaksaan</th>
                        <th>Jenis Barang Bukti</th>
                        <th>Pasal Yang Didakwakan</th>
                        <th>Register Benda Sitaan Barang Bukti / Tanggal Penerimaan Barang Bukti</th>
                        <th>Macam Jenis Kadar</th>
                        <th>Jumlah Satuan</th>
                        <th>Jenis Satuan</th>
                        <th>Tempat Penyimpanan</th>
                        <th>Tgl & No. KEP PN/PT/MA</th>
                        <th>Amar Putusan</th>
                        <th>Tanggal Pelaksanaan Putusan Hakim</th>
                    </tr>
                    <tr class="noth">
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                        <th>7</th>
                        <th>8</th>
                        <th>9</th>
                        <th>10</th>
                        <th>11</th>
                        <th>12</th>
                    </tr>

                    @forelse($cases as $idx => $case)
                        @php 
                            $bbRaw = $case['barangBuktiList'] ?? [];
                            $bbList = (is_array($bbRaw) && count($bbRaw) > 0) ? array_values($bbRaw) : []; 

                            $tpList = array_values(array_filter(array_map(function($item) {
                                return trim($item['tempatPenyimpanan'] ?? $item['tempat_penyimpanan'] ?? '');
                            }, $bbList)));

                            $uniqueTp = array_values(array_unique($tpList));
                            $isSingleTp = count($uniqueTp) <= 1;
                            $singleTpText = count($uniqueTp) === 1 ? $uniqueTp[0] : '-';

                            $tpGroups = [];
                            foreach ($bbList as $bIdx => $bb) {
                                $tpName = trim($bb['tempatPenyimpanan'] ?? $bb['tempat_penyimpanan'] ?? '') ?: '-';
                                if (empty($tpGroups) || $tpGroups[count($tpGroups) - 1]['name'] !== $tpName) {
                                    $tpGroups[] = [
                                        'name' => $tpName,
                                        'count' => 1,
                                    ];
                                } else {
                                    $tpGroups[count($tpGroups) - 1]['count']++;
                                }
                            }
                        @endphp

                        <tr style="page-break-inside: avoid !important;">
                            {{-- 1. No. Urut --}}
                            <td class="text-center">{{ $idx + 1 }}</td>

                            {{-- 2. Kejaksaan --}}
                            <td class="satker">{{ $case['satuanKerja'] ?? '-' }}</td>

                            {{-- 3. Jenis Barang Bukti --}}
                            <td class="text-left text-top">
                                @forelse($bbList as $bIdx => $bb)
                                    <div style="{{ !$loop->last ? 'margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed #ccc;' : '' }}">
                                        - {{ formatJumlah($bb['jumlah'] ?? null) }} {{ $bb['jenisBarangBukti'] ?? '' }} {{ $bb['uraianBarangBukti'] ?? '' }}
                                    </div>
                                @empty
                                    -
                                @endforelse
                            </td>

                            {{-- 4. Pasal --}}
                            <td class="text-center">{{ $case['pasalDidakwakan'] ?? '-' }}</td>

                            {{-- 5. Reg Sitaan --}}
                            <td>
                                {{ $case['noRegBendaSitaan'] ?? '-' }}<br>
                                {{ !empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-' ? \Carbon\Carbon::parse($case['tglPenerimaan'])->locale('id')->translatedFormat('d F Y') : '' }}
                            </td>

                            {{-- 6. Macam Jenis Kadar --}}
                            <td>
                                @forelse($bbList as $bIdx => $bb)
                                    <div style="{{ !$loop->last ? 'margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed #ccc;' : '' }}">
                                        - {{ $bb['macamJenisKadar'] ?? '-' }}
                                    </div>
                                @empty
                                    -
                                @endforelse
                            </td>

                            {{-- 7. Jumlah Satuan --}}
                            <td>
                                @forelse($bbList as $bIdx => $bb)
                                    <div style="{{ !$loop->last ? 'margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed #ccc;' : '' }}">
                                        - {{ formatJumlah($bb['jumlah'] ?? null) }}
                                    </div>
                                @empty
                                    -
                                @endforelse
                            </td>

                            {{-- 8. Jenis Satuan --}}
                            <td>
                                @forelse($bbList as $bIdx => $bb)
                                    <div style="{{ !$loop->last ? 'margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed #ccc;' : '' }}">
                                        - {{ $bb['satuan'] ?? '-' }}
                                    </div>
                                @empty
                                    -
                                @endforelse
                            </td>

                            {{-- 9. Tempat Penyimpanan --}}
                            <td class="wrap-text text-center">
                                @if($isSingleTp)
                                    {{ $singleTpText }}
                                @else
                                    @foreach($tpGroups as $gIdx => $group)
                                        <div style="{{ !$loop->last ? 'margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px dashed #aaa;' : '' }} padding-top: 2px;">
                                            {{ $group['name'] }}
                                        </div>
                                    @endforeach
                                @endif
                            </td>

                            {{-- 10. No KEP --}}
                            <td>
                                {{ $case['noKepPengadilan'] ?? '-' }}<br>
                                {{ !empty($case['tglKepPengadilan']) && $case['tglKepPengadilan'] !== '-' ? \Carbon\Carbon::parse($case['tglKepPengadilan'])->locale('id')->translatedFormat('d F Y') : '' }}
                            </td>

                            {{-- 11. Amar Putusan --}}
                            <td class="text-left">
                                @forelse($bbList as $bIdx => $bb)
                                    @php
                                        $isSdaAmar = false;
                                        if ($bIdx > 0 && isset($bbList[$bIdx - 1])) {
                                            $prevAmar = trim(($bbList[$bIdx - 1]['amarPutusan'] ?? '') . ' ' . ($bbList[$bIdx - 1]['uraianPutusan'] ?? ''));
                                            $currAmar = trim(($bb['amarPutusan'] ?? '') . ' ' . ($bb['uraianPutusan'] ?? ''));
                                            if ($prevAmar !== '' && $prevAmar === $currAmar) {
                                                $isSdaAmar = true;
                                            }
                                        }
                                    @endphp
                                    <div style="{{ !$loop->last ? 'margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed #ccc;' : '' }}">
                                        @if($isSdaAmar)
                                            - Sda
                                        @else
                                            - {{ $bb['amarPutusan'] ?? '' }} {{ $bb['uraianPutusan'] ?? '' }}
                                        @endif
                                    </div>
                                @empty
                                    -
                                @endforelse
                            </td>

                            {{-- 12. Tgl Pelaksanaan --}}
                            <td>
                                {{ !empty($case['tglPelaksanaanPutusan']) && $case['tglPelaksanaanPutusan'] !== '-' ? \Carbon\Carbon::parse($case['tglPelaksanaanPutusan'])->locale('id')->translatedFormat('d F Y') : ($case['tglPelaksanaanPutusan'] ?? '-') }}
                            </td>
                        </tr>
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
                <td width="60%"></td>
                <td width="40%">
                    Banda Aceh, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                    <b>Pth. {{ $jabatanKasi }}</b>

                    <div class="ttd-space"></div>

                    <b><u>{{ $namaKasi }}</u></b><br>
                    <b>{{ $pangkatKasi ? $pangkatKasi . ' / ' : '' }}NIP. {{ $nipKasi }}</b>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>