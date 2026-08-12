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

    $jabatanKasi = $pejabatData['jabatan_kasi'];
    $namaKasi = $pejabatData['nama_kasi'];
    $nipKasi = $pejabatData['nip_kasi'];
    $pangkatKasi = $pejabatData['pangkat_kasi'];

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
            
            // Jika nilai angka murni (cth: 1, 10, "5")
            if (is_numeric($val)) {
                $teks = trim(preg_replace('/\s+/', ' ', terbilang($val)));
                return $teks ? "{$val} ({$teks})" : $val;
            }
            
            // Jika sudah berupa teks / campuran (cth: "1 unit")
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
            size: 841.9pt 595.3pt;
            mso-page-orientation: landscape;
            margin: 1.2in 0.5in 0.5in 0.5in;
            mso-header: h1;
        }
        
        div.Section1 {
            page: Section1;
        }

        div.WordHeader {
            mso-element: header;
            id: h1;
        }

        p.MsoHeader, li.MsoHeader, div.MsoHeader {
            margin: 0pt;
            margin-bottom: .0001pt;
            mso-pagination: widow-orphan;
        }

        body { font-family: Arial, sans-serif; font-size: 12pt; }
        .title { text-align: center; font-weight: bold; font-size: 14pt; margin-bottom: 15px; text-transform: uppercase; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #000000; padding: 5px; text-align: center; font-size: 9pt; }
        th { font-weight: bold;}
        .text-left { text-align: left; }
        .tp { text-align: center; font-weight: bold; margin-bottom: 1rem; font-size: 14pt; }
        .satker { font-weight: bold; }
        .header-container { width: 100%; border-collapse: collapse; margin-bottom: 20px; margfin-top: -1.5rem; }
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

        .ttd-space {
            height: 65px; 
        }
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
                <thead>
                    <tr>
                        <th width="3%">No. Urut</th>
                        <th>Satuan Kerja</th>
                        <th>Register Benda Sitaan Barang Bukti</th>
                        <th>Register Tahap Penyidikan</th>
                        <th>Uraian Benda Sitaan Jumlah / Satuan/ Jenis / Barang / Ukuran</th>
                        <th>Tempat Penyimpanan</th>
                        <th>Identitas Tersangka / Terdakwa</th>
                        <th>Pasal yang disangkakan / didakwakan</th>
                        <th>Diselesaikan</th>
                        <th>Tanggal Pelaksanaan Putusan Hakim & Ijin Jaksa Agung</th>
                        <th>Keterangan</th>
                    </tr>
                    <tr class="noth">
                        <th width="3%">1</th>
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
                </thead>
                <tbody>
                    @forelse($cases as $idx => $case)
                        @php 
                            $bbList = (isset($case['barangBuktiList']) && count($case['barangBuktiList']) > 0) ? $case['barangBuktiList'] : [null]; 
                            $rowSpan = count($bbList);
                        @endphp

                        @foreach($bbList as $bIdx => $bb)
                        <tr>
                            @if($bIdx === 0)
                                <td rowspan="{{ $rowSpan }}">{{ $idx + 1 }}</td>
                                <td rowspan="{{ $rowSpan }}" class="satker">{{ $case['satuanKerja'] }}</td>
                                <td rowspan="{{ $rowSpan }}">{{ $case['noRegBendaSitaan'] }}<br><small>
                                    {{ !empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-' ? \Carbon\Carbon::parse($case['tglPenerimaan'])->locale('id')->translatedFormat('d F Y') : '-' }}
                                </small></td>
                                <td rowspan="{{ $rowSpan }}">{{ $case['noRegPenyidikan'] ?? '-' }}<br><small>
                                    {{ !empty($case['tglRegPenyidikan']) && $case['tglRegPenyidikan'] !== '-' ? \Carbon\Carbon::parse($case['tglRegPenyidikan'])->locale('id')->translatedFormat('d F Y') : '-' }}
                                </small></td>
                            @endif

                            <!-- Detail Barang Bukti -->
                            <td class="text-left">
                                @if($bb)
                                    {{ formatJumlah($bb['jumlah']) }} {{ $bb['satuan'] }} {{ $bb['uraianBarangBukti'] ?? $bb['jenisBarangBukti'] }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $bb['tempatPenyimpanan'] }}</td>

                            @if($bIdx === 0)
                                <td rowspan="{{ $rowSpan }}" class="text-left">{{ $case['identitasTersangka'] }}</td>
                                <td rowspan="{{ $rowSpan }}">{{ $case['pasalDisangkakan'] }}</td>
                                <td rowspan="{{ $rowSpan }}">{{ $case['statusDiselesaikan'] }}</td>
                                <td rowspan="{{ $rowSpan }}">{{ $case['tglPelaksanaanPutusan'] }}</td>
                                <td rowspan="{{ $rowSpan }}">{{ $case['keterangan'] }}</td>
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
        @elseif(($filters['formType']) === '3C')
            <table>
                <thead>
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
                        <th width="3%">1</th>
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
                </thead>
                <tbody>
                    @forelse($cases as $idx => $case)
                        @php 
                            $bbList = (isset($case['barangBuktiList']) && count($case['barangBuktiList']) > 0) ? $case['barangBuktiList'] : [null]; 
                            $rowSpan = count($bbList);
                        @endphp

                        @foreach($bbList as $bIdx => $bb)
                        <tr>
                            @if($bIdx === 0)
                                <td rowspan="{{ $rowSpan }}">{{ $idx + 1 }}</td>
                                <td rowspan="{{ $rowSpan }}" class="satker">{{ $case['satuanKerja'] }}</td>
                            @endif

                            <!-- Detail Barang Bukti -->
                            <td class="text-left">
                                @if($bb)
                                    - {{ formatJumlah($bb['jumlah']) }} {{ $bb['jenisBarangBukti'] }} {{ $bb['uraianBarangBukti'] }}
                                @else
                                    -
                                @endif
                            </td>
                            
                            @if($bIdx === 0)
                                <td rowspan="{{ $rowSpan }}" class="text-center">
                                    {{ $case['pasalDidakwakan'] }}
                                </td>
                                <td rowspan="{{ $rowSpan }}">
                                    {{ $case['noRegBendaSitaan'] }}<br>
                                    {{ !empty($case['tglPenerimaan']) && $case['tglPenerimaan'] !== '-' ? \Carbon\Carbon::parse($case['tglPenerimaan'])->locale('id')->translatedFormat('d F Y') : '' }}
                                </td>
                            @endif

                            <td>- {{ $bb['macamJenisKadar'] }}</td>
                            <td>- {{ formatJumlah($bb['jumlah']) }}</td>
                            <td>- {{ $bb['satuan'] }}</td>

                            <td>{{ $bb['tempatPenyimpanan'] }}</td>

                            @if($bIdx === 0)
                                <td rowspan="{{ $rowSpan }}">
                                    {{ $case['noKepPengadilan'] }}<br>
                                    {{ !empty($case['tglKepPengadilan']) && $case['tglKepPengadilan'] !== '-' ? \Carbon\Carbon::parse($case['tglKepPengadilan'])->locale('id')->translatedFormat('d F Y') : '' }}
                                </td>
                            @endif

                            <td class="text-left">
                                @if($bb)
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

                                    @if($isSdaAmar)
                                        - Sda
                                    @else
                                        - {{ $bb['amarPutusan'] }} {{ $bb['uraianPutusan'] }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>

                            @if($bIdx === 0)
                                <td rowspan="{{ $rowSpan }}">
                                    {{ !empty($case['tglPelaksanaanPutusan']) && $case['tglPelaksanaanPutusan'] !== '-' ? \Carbon\Carbon::parse($case['tglPelaksanaanPutusan'])->locale('id')->translatedFormat('d F Y') : ($case['tglPelaksanaanPutusan'] ?? '-') }}
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