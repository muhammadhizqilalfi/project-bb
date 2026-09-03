<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FormTemplateController extends Controller
{
    public function index3A()
    {
        return Inertia::render('Tabs/Form3A', [
            'forms' => $this->getFormsByType('3A'),
        ]);
    }

    public function index3C()
    {
        return Inertia::render('Tabs/Form3C', [
            'forms' => $this->getFormsByType('3C'),
        ]);
    }

    public function index3B(Request $request)
    {
        $month = (int) $request->input('month', 7);
        $year = (int) $request->input('year', 2026);
        $kategori = $request->input('kategori', 'ALL');

        // 1. Cek apakah ada record Form 3B tersimpan di database
        $form3B = FormTemplate::where('form_type', '3B')
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $cases = [];

        if ($form3B) {
            $rawCases = $form3B->cases;
            if (is_string($rawCases)) {
                $rawCases = json_decode($rawCases, true);
            }

            if (is_array($rawCases)) {
                foreach ($rawCases as $c) {
                    $katCase = $c['kategoriTindakPidana'] ?? '';
                    
                    if ($kategori !== 'ALL' && !$this->matchCategory($katCase, $kategori)) {
                        continue;
                    }

                    $jumlah = (int) ($c['jumlahBulanLaporan'] ?? 0);
                    $sisa = (int) ($c['sisaBulanLaporan'] ?? 0);
                    $selesai = isset($c['perkaraSelesai']) ? (int) $c['perkaraSelesai'] : max(0, $jumlah - $sisa);

                    $cases[] = [
                        'satuanKerja'          => $c['satuanKerja'] ?? 'Kejari Banda Aceh',
                        'kategoriTindakPidana' => $c['kategoriTindakPidana'] ?? '-',
                        'sisaBulanLalu'        => (int) ($c['sisaBulanLalu'] ?? 0),
                        'masukBulanLaporan'    => (int) ($c['masukBulanLaporan'] ?? 0),
                        'jumlahBulanLaporan'   => $jumlah,
                        'perkaraSelesai'       => $selesai,
                        'sisaBulanLaporan'     => $sisa,
                        'keterangan'           => $c['keterangan'] ?? '-',
                    ];
                }
            }
        }

        // 2. Jika data DB kosong, hitung kalkulasi otomatis dari Form 3A & 3C
        if (empty($cases)) {
            $categoriesToProcess = ($kategori === 'ALL') ? [
                'KAMNEGTIBUM DAN TPUL',
                'NARKOTIKA DAN ZAT ADIKTIF LAINNYA',
                'OHARDA',
                'TERORIS',
                'KORUPSI'
            ] : [$kategori];

            foreach ($categoriesToProcess as $cat) {
                $sisaBulanLalu = $this->calculateSisaBulanLalu($month, $year, $cat);
                $masukBulanLaporan = $this->countCasesFromForm('3A', $month, $year, $cat);
                $jumlahBulanLaporan = $sisaBulanLalu + $masukBulanLaporan;
                $perkaraSelesai = $this->countCasesFromForm('3C', $month, $year, $cat);
                $sisaBulanLaporan = max(0, $jumlahBulanLaporan - $perkaraSelesai);

                $cases[] = [
                    'satuanKerja'          => 'Kejari Banda Aceh',
                    'kategoriTindakPidana' => $cat,
                    'sisaBulanLalu'        => $sisaBulanLalu,
                    'masukBulanLaporan'    => $masukBulanLaporan,
                    'jumlahBulanLaporan'   => $jumlahBulanLaporan,
                    'perkaraSelesai'       => $perkaraSelesai,
                    'sisaBulanLaporan'     => $sisaBulanLaporan,
                    'keterangan'           => '-',
                ];
            }
        }

        $monthNames = [
            1 => 'JANUARI', 'FEBRUARI', 'MARET', 'APRIL', 'MEI', 'JUNI',
            'JULI', 'AGUSTUS', 'SEPTEMBER', 'OKTOBER', 'NOVEMBER', 'DESEMBER'
        ];

        return Inertia::render('Tabs/Form3B', [
            'filters' => [
                'month' => $month,
                'year' => $year,
                'kategori' => $kategori,
                'selectedPeriod' => ($monthNames[$month] ?? 'JULI') . ' ' . $year,
            ],
            'cases' => $cases,
            'calculatedData' => $cases[0] ?? null
        ]);
    }

    // 3A
    public function create3AWizard()
    {
        return Inertia::render('Tabs/Form3AInput', [
            'form' => null,
            'caseData' => null,
            'dropdownOptions' => $this->getDropdownOptionsForForm('3A'),
        ]);
    }

    public function create3CWizard()
    {
        return Inertia::render('Tabs/Form3CInput', [
            'form' => null,
            'caseData' => null,
            'dropdownOptions' => $this->getDropdownOptionsForForm('3C'),
        ]);
    }

    public function store3AWizard(Request $request)
    {
        $validated = $request->validate([
            'header.name' => 'required|string|max:255',
            'header.month' => 'required|integer|min:1|max:12',
            'header.year' => 'required|integer|min:2000|max:2100',
            
            'case.satuanKerja' => 'required|string|max:255',
            'case.kategoriTindakPidana' => 'required|string|max:255',
            'case.noRegBendaSitaan' => 'required|string|max:255',
            'case.tglPenerimaan' => 'nullable|string',
            'case.noRegPenyidikan' => 'required|string|max:255',
            'case.tglRegPenyidikan' => 'nullable|string',
            'case.identitasTersangka' => 'required|string',
            'case.pasalDisangkakan' => 'required|string|max:255',
            'case.statusDiselesaikan' => 'required|string|max:255',
            'case.tglPelaksanaanPutusan' => 'nullable|string',
            'case.keterangan' => 'nullable|string',

            'case.barangBuktiList' => 'required|array|min:1',
            'case.barangBuktiList.*.jenisBarangBukti' => 'nullable|string|max:255',
            'case.barangBuktiList.*.jumlah' => 'required|numeric',
            'case.barangBuktiList.*.uraianBarangBukti' => 'required|string',
            'case.barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
            'case.barangBuktiList.*.jenisNarkotika' => 'nullable|string|max:255',
            'case.barangBuktiList.*.jumlahNarkotika' => 'nullable|numeric',
            'case.barangBuktiList.*.satuanNarkotika' => 'nullable|string|max:255',
        ]);

        $caseData = [
            'satuanKerja' => $validated['case']['satuanKerja'],
            'kategoriTindakPidana' => $validated['case']['kategoriTindakPidana'],
            'noRegBendaSitaan' => $validated['case']['noRegBendaSitaan'],
            'tglPenerimaan' => $validated['case']['tglPenerimaan'] ?? '-',
            'noRegPenyidikan' => $validated['case']['noRegPenyidikan'],
            'tglRegPenyidikan' => $validated['case']['tglRegPenyidikan'] ?? '-',
            'identitasTersangka' => $validated['case']['identitasTersangka'],
            'pasalDisangkakan' => $validated['case']['pasalDisangkakan'],
            'statusDiselesaikan' => $validated['case']['statusDiselesaikan'],
            'tglPelaksanaanPutusan' => $validated['case']['tglPelaksanaanPutusan'] ?? null,
            'keterangan' => $validated['case']['keterangan'] ?? null,
            'barangBuktiList' => $validated['case']['barangBuktiList'],
        ];

        FormTemplate::create([
            'form_type' => '3A',
            'name' => $validated['header']['name'],
            'month' => (int) $validated['header']['month'],
            'year' => (int) $validated['header']['year'],
            'cases' => [$caseData],
            'latest_case_summary' => $caseData,
            'latest_case_saved_at' => now(),
        ]);

        return redirect('/laporan')->with('success', 'Form dan case berhasil disimpan');
    }

    public function edit3A(Request $request, string $id)
    {
        $form = $this->findForm('3A', $id);
        $caseIndex = $request->query('index');

        $cases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

        if ($caseIndex !== null && isset($cases[$caseIndex])) {
            $summary = $cases[$caseIndex];
        } else {
            $summary = $form->latest_case_summary ?? [];
        }

        $caseData = array_merge($summary, [
            'id' => (string) $form->id,
            'case_index' => $caseIndex !== null ? (int) $caseIndex : (count($cases) - 1),
        ]);

        return Inertia::render('Tabs/Form3AInput', [
            'form' => $this->mapForm($form),
            'caseData' => $caseData,
            'dropdownOptions' => $this->getDropdownOptionsForForm('3A'),
        ]);
    }

    public function update3A(Request $request, string $id)
    {
        $validated = $request->validate([
            'case_index' => 'nullable|integer',
            'satuanKerja' => 'required|string|max:255',
            'kategoriTindakPidana' => 'required|string|max:255',
            'noRegBendaSitaan' => 'required|string|max:255',
            'tglPenerimaan' => 'nullable|string',
            'noRegPenyidikan' => 'required|string|max:255',
            'tglRegPenyidikan' => 'nullable|string',
            'identitasTersangka' => 'required|string',
            'pasalDisangkakan' => 'required|string|max:255',
            'statusDiselesaikan' => 'required|string|max:255',
            'tglPelaksanaanPutusan' => 'nullable|string',
            'keterangan' => 'nullable|string',

            'barangBuktiList' => 'required|array|min:1',
            'barangBuktiList.*.jenisBarangBukti' => 'nullable|string|max:255',
            'barangBuktiList.*.jumlah' => 'required|numeric',
            'barangBuktiList.*.uraianBarangBukti' => 'required|string',
            'barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
            'barangBuktiList.*.jenisNarkotika' => 'nullable|string|max:255',
            'barangBuktiList.*.jumlahNarkotika' => 'nullable|numeric',
            'barangBuktiList.*.satuanNarkotika' => 'nullable|string|max:255',
        ]);

        $form = $this->findForm('3A', $id);
        $cases = $form->cases ?? [];

        $caseIndex = $validated['case_index'] ?? (count($cases) - 1);
        unset($validated['case_index']);

        if (isset($cases[$caseIndex])) {
            $cases[$caseIndex] = $validated;
        } else {
            $cases[] = $validated;
        }
        
        $form->update([
            'cases' => $cases,
            'latest_case_summary' => $validated,
            'latest_case_saved_at' => now(),
        ]);

        return redirect('/laporan')->with('success', 'Data perkara berhasil diperbarui');
    }

    public function store3ACase(Request $request, string $id)
    {
        $validated = $request->validate([
            'satuanKerja' => 'required|string|max:255',
            'kategoriTindakPidana' => 'required|string|max:255',
            'noRegBendaSitaan' => 'required|string|max:255',
            'tglPenerimaan' => 'nullable|string',
            'noRegPenyidikan' => 'required|string|max:255',
            'tglRegPenyidikan' => 'nullable|string',
            'identitasTersangka' => 'required|string',
            'pasalDisangkakan' => 'required|string|max:255',
            'statusDiselesaikan' => 'required|string|max:255',
            'tglPelaksanaanPutusan' => 'nullable|string',
            'keterangan' => 'nullable|string',

            'barangBuktiList' => 'required|array|min:1',
            'barangBuktiList.*.jenisBarangBukti' => 'nullable|string|max:255',
            'barangBuktiList.*.jumlah' => 'required|numeric',
            'barangBuktiList.*.uraianBarangBukti' => 'required|string',
            'barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
            'barangBuktiList.*.jenisNarkotika' => 'nullable|string|max:255',
            'barangBuktiList.*.jumlahNarkotika' => 'nullable|numeric',
            'barangBuktiList.*.satuanNarkotika' => 'nullable|string|max:255',
        ]);

        $form = $this->findForm('3A', $id);

        $existingCases = $form->cases ?? [];

        if (empty($existingCases) && !empty($form->latest_case_summary)) {
            $existingCases = [$form->latest_case_summary];
        }

        if (!is_array($existingCases)) {
            $existingCases = [];
        }

        $existingCases[] = $validated;

        $form->update([
            'cases' => $existingCases,
            'latest_case_summary' => $validated,
            'latest_case_saved_at' => now(),
        ]);

        return redirect('/laporan')->with('success', 'Case berhasil ditambahkan');
    }

    public function destroy3A(Request $request, string $id)
    {
        $form = $this->findForm('3A', $id);
        $caseIndex = $request->query('index');

        $cases = $form->cases ?? [];

        if ($caseIndex !== null && isset($cases[$caseIndex])) {
            array_splice($cases, (int) $caseIndex, 1);

            if (count($cases) > 0) {
                $form->update([
                    'cases' => $cases,
                    'latest_case_summary' => end($cases),
                ]);
                return redirect()->back()->with('success', 'Data perkara berhasil dihapus');
            }
        }

        $form->delete();
        return redirect()->back()->with('success', 'Data perkara berhasil dihapus');
    }

    // 3C
    public function store3CWizard(Request $request)
    {
        $validated = $request->validate([
            'header.name' => 'required|string|max:255',
            'header.month' => 'required|integer|min:1|max:12',
            'header.year' => 'required|integer|min:2000|max:2100',

            'case.satuanKerja' => 'required|string|max:255',
            'case.kategoriTindakPidana' => 'required|string|max:255',
            'case.pasalDidakwakan' => 'required|string|max:255',
            'case.noRegBendaSitaan' => 'required|string|max:255',
            'case.tglPenerimaan' => 'nullable|string',
            'case.noKepPengadilan' => 'required|string|max:255',
            'case.tglKepPengadilan' => 'nullable|string',
            'case.tglPelaksanaanPutusan' => 'nullable|string',

            'case.barangBuktiList' => 'required|array|min:1',
            'case.barangBuktiList.*.jumlahSatuan' => 'required|numeric',
            'case.barangBuktiList.*.uraianBarangBukti' => 'required|string',
            'case.barangBuktiList.*.jenisSatuan' => 'required|string|max:255',
            'case.barangBuktiList.*.macamJenisKadar' => 'required|string',
            'case.barangBuktiList.*.amarPutusan' => 'required|string|max:255',
            'case.barangBuktiList.*.uraianPutusan' => 'nullable|string',
            'case.barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
        ]);

        $caseData = [
            'satuanKerja' => $validated['case']['satuanKerja'],
            'kategoriTindakPidana' => $validated['case']['kategoriTindakPidana'],
            'pasalDidakwakan' => $validated['case']['pasalDidakwakan'],
            'noRegBendaSitaan' => $validated['case']['noRegBendaSitaan'],
            'tglPenerimaan' => $validated['case']['tglPenerimaan'] ?? '-',
            'noKepPengadilan' => $validated['case']['noKepPengadilan'],
            'tglKepPengadilan' => $validated['case']['tglKepPengadilan'] ?? '-',
            'tglPelaksanaanPutusan' => $validated['case']['tglPelaksanaanPutusan'] ?? null,
            'barangBuktiList' => $validated['case']['barangBuktiList'],
        ];

        FormTemplate::create([
            'form_type' => '3C',
            'name' => $validated['header']['name'],
            'month' => (int) $validated['header']['month'],
            'year' => (int) $validated['header']['year'],
            'cases' => [$caseData],
            'latest_case_summary' => $caseData,              
            'latest_case_saved_at' => now(),
        ]);

        return redirect('/laporan')->with('success', 'Form 3C berhasil disimpan');
    }

    public function edit3C(Request $request, string $id)
    {
        $form = $this->findForm('3C', $id);
        $caseIndex = $request->query('index');

        $cases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);

        if ($caseIndex !== null && isset($cases[$caseIndex])) {
            $summary = $cases[$caseIndex];
        } else {
            $summary = $form->latest_case_summary ?? [];
        }

        $caseData = array_merge($summary, [
            'id' => (string) $form->id,
            'case_index' => $caseIndex !== null ? (int) $caseIndex : (count($cases) - 1),
        ]);

        return Inertia::render('Tabs/Form3CInput', [
            'form' => $this->mapForm($form),
            'caseData' => $caseData,
            'dropdownOptions' => $this->getDropdownOptionsForForm('3C'),
        ]);
    }

    public function update3C(Request $request, string $id)
    {
        $validated = $request->validate([
            'case_index' => 'nullable|integer',
            'satuanKerja' => 'required|string|max:255',
            'kategoriTindakPidana' => 'required|string|max:255',
            'pasalDidakwakan' => 'required|string|max:255',
            'noRegBendaSitaan' => 'required|string|max:255',
            'tglPenerimaan' => 'nullable|string',
            'noKepPengadilan' => 'required|string|max:255',
            'tglKepPengadilan' => 'nullable|string',
            'tglPelaksanaanPutusan' => 'nullable|string',

            'barangBuktiList' => 'required|array|min:1',
            'barangBuktiList.*.jumlahSatuan' => 'required|numeric',
            'barangBuktiList.*.uraianBarangBukti' => 'required|string',
            'barangBuktiList.*.jenisSatuan' => 'required|string|max:255',
            'barangBuktiList.*.macamJenisKadar' => 'required|string',
            'barangBuktiList.*.amarPutusan' => 'required|string|max:255',
            'barangBuktiList.*.uraianPutusan' => 'nullable|string',
            'barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
        ]);

        $form = $this->findForm('3C', $id);
        $cases = $form->cases ?? [];

        $caseIndex = $validated['case_index'] ?? (count($cases) - 1);
        unset($validated['case_index']);

        if (isset($cases[$caseIndex])) {
            $cases[$caseIndex] = $validated;
        } else {
            $cases[] = $validated;
        }

        $form->update([
            'cases' => $cases,
            'latest_case_summary' => $validated,
            'latest_case_saved_at' => now(),
        ]);

        return redirect('/laporan')->with('success', 'Data perkara 3C berhasil diperbarui');
    }

    public function store3CCase(Request $request, string $id)
    {
        $validated = $request->validate([
            'satuanKerja' => 'required|string|max:255',
            'kategoriTindakPidana' => 'required|string|max:255',
            'pasalDidakwakan' => 'required|string|max:255',
            'noRegBendaSitaan' => 'required|string|max:255',
            'tglPenerimaan' => 'nullable|string',
            'noKepPengadilan' => 'required|string|max:255',
            'tglKepPengadilan' => 'nullable|string',
            'tglPelaksanaanPutusan' => 'nullable|string',

            'barangBuktiList' => 'required|array|min:1',
            'barangBuktiList.*.jumlahSatuan' => 'required|numeric',
            'barangBuktiList.*.uraianBarangBukti' => 'required|string',
            'barangBuktiList.*.jenisSatuan' => 'required|string|max:255',
            'barangBuktiList.*.macamJenisKadar' => 'required|string',
            'barangBuktiList.*.amarPutusan' => 'required|string|max:255',
            'barangBuktiList.*.uraianPutusan' => 'nullable|string',
            'barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
        ]);

        $form = $this->findForm('3C', $id);

        $existingCases = $form->cases;

        if (empty($existingCases) && !empty($form->latest_case_summary)) {
            $existingCases = [$form->latest_case_summary];
        }

        if (!is_array($existingCases)) {
            $existingCases = [];
        }

        $existingCases[] = $validated;

        $form->update([
            'cases' => $existingCases,
            'latest_case_summary' => $validated,
            'latest_case_saved_at' => now(),
        ]);

        return redirect('/laporan')->with('success', 'Case 3C berhasil ditambahkan');
    }

    public function destroy3C(Request $request, string $id)
    {
        $form = $this->findForm('3C', $id);
        $caseIndex = $request->query('index');

        $cases = $form->cases ?? [];

        if ($caseIndex !== null && isset($cases[$caseIndex])) {
            array_splice($cases, (int) $caseIndex, 1);

            if (count($cases) > 0) {
                $form->update([
                    'cases' => $cases,
                    'latest_case_summary' => end($cases),
                ]);
                return redirect()->back()->with('success', 'Data perkara 3C berhasil dihapus');
            }
        }

        $form->delete();
        return redirect()->back()->with('success', 'Data perkara 3C berhasil dihapus');
    }

    public function create3ACase(string $id)
    {
        return Inertia::render('Tabs/Form3AInput', [
            'form' => $this->mapForm($this->findForm('3A', $id)),
            'caseData' => null,
            'dropdownOptions' => $this->getDropdownOptionsForForm('3A'),
        ]);
    }

    public function create3CCase(string $id)
    {
        return Inertia::render('Tabs/Form3CInput', [
            'form' => $this->mapForm($this->findForm('3C', $id)),
            'caseData' => null,
            'dropdownOptions' => $this->getDropdownOptionsForForm('3C'),
        ]);
    }

    // ==========================================
    // FORM 3D (Lelang Barang Rampasan)
    // ==========================================
    public function index3D()
    {
        $forms = FormTemplate::where('form_type', '3D')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return Inertia::render('Tabs/Form3D', [
            'forms' => $forms,
        ]);
    }

    public function store3DForm(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:2100',
        ]);

        $exists = FormTemplate::where('form_type', '3D')
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($exists) {
            return back()->with('error', 'Form 3D untuk periode tersebut sudah ada.');
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $monthNames[(int)$request->month] ?? '';

        FormTemplate::create([
            'form_type' => '3D',
            'name'      => "FORM 3D {$namaBulan} {$request->year}",
            'month'     => $request->month,
            'year'      => $request->year,
            'cases'     => [],
        ]);

        return back()->with('success', 'Form Induk 3D berhasil dibuat.');
    }

    public function destroy3DForm($formId)
    {
        $form = FormTemplate::findOrFail($formId);
        $form->delete();
        return back()->with('success', 'Form 3D berhasil dihapus.');
    }

    public function create3DCase($formId)
    {
        $form = FormTemplate::findOrFail($formId);

        // Ambil opsi dari database dengan filter unique() agar tidak berulang
        $instansiPenilai = DropdownOption::where('category', 'instansi_penilai')
            ->whereIn('form_target', ['3D', 'Keduanya'])
            ->pluck('label')
            ->unique()
            ->values()
            ->toArray();

        $statusLelang = DropdownOption::where('category', 'status_lelang')
            ->whereIn('form_target', ['3D', 'Keduanya'])
            ->pluck('label')
            ->unique()
            ->values()
            ->toArray();

        if (empty($instansiPenilai)) {
            $instansiPenilai = ['KPKNL', 'KJPP'];
        }
        if (empty($statusLelang)) {
            $statusLelang = ['LAKU', 'BELUM_LAKU', 'PROSES'];
        }

        // Tarik opsi keterangan_3d
        $keteranganOptions = DropdownOption::where('category', 'keterangan_3d')->pluck('label')->unique()->values()->toArray();

        return Inertia::render('Tabs/Form3DInput', [
            'formId' => $form->id,
            'month'  => $form->month,
            'year'   => $form->year,
            'dropdownOptions' => [
                'instansi_penilai' => $instansiPenilai,
                'status_lelang'    => $statusLelang,
                'keterangan_options' => $keteranganOptions,
            ],
        ]);
    }

    public function store3DCase(Request $request, $formId)
    {
        $form = FormTemplate::findOrFail($formId);

        $validated = $request->validate([
            'satuanKerja'              => 'nullable|string',
            'terpidana_nama'         => 'required|string',
            'tgl_penyerahan'         => 'required|string',
            'putusan_no'             => 'required|string',
            'putusan_tgl'            => 'required|string',
            'perkara'                => 'required|string',
            'items'                  => 'required|array|min:1',
            'items.*.nama_barang'      => 'required|string',
            'items.*.harga_taksiran'   => 'nullable|numeric',
            'items.*.instansi_penilai' => 'nullable|string',
            'items.*.tgl_penilaian'    => 'nullable|string',
            'items.*.nilai_laku'       => 'nullable|numeric',
            'items.*.status_lelang'    => 'nullable|string',
            'items.*.keterangan'       => 'nullable|string',
        ]);

        $cases = $form->cases ?? [];
        if (is_string($cases)) {
            $cases = json_decode($cases, true);
        }

        $cases[] = $validated;
        $form->update(['cases' => $cases]);

        return redirect()->route('form3d.index')->with('success', 'Kasus Form 3D berhasil ditambahkan.');
    }

    public function edit3DCase($formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) {
            $cases = json_decode($cases, true);
        }

        if (!isset($cases[$index])) {
            return redirect()->route('form3d.index')->with('error', 'Data kasus tidak ditemukan.');
        }

        $instansiPenilai = DropdownOption::where('category', 'instansi_penilai')
            ->whereIn('form_target', ['3D', 'Keduanya'])
            ->pluck('label')
            ->unique()
            ->values()
            ->toArray();

        $statusLelang = DropdownOption::where('category', 'status_lelang')
            ->whereIn('form_target', ['3D', 'Keduanya'])
            ->pluck('label')
            ->unique()
            ->values()
            ->toArray();

        if (empty($instansiPenilai)) {
            $instansiPenilai = ['KPKNL', 'KJPP'];
        }
        if (empty($statusLelang)) {
            $statusLelang = ['LAKU', 'BELUM_LAKU', 'PROSES'];
        }

        // Tarik opsi keterangan_3d
        $keteranganOptions = DropdownOption::where('category', 'keterangan_3d')->pluck('label')->unique()->values()->toArray();
        
        return Inertia::render('Tabs/Form3DInput', [
            'formId'    => $form->id,
            'caseIndex' => (int) $index,
            'caseData'  => $cases[$index],
            'month'     => $form->month,
            'year'      => $form->year,
            'dropdownOptions' => [
                'instansi_penilai' => $instansiPenilai,
                'status_lelang'    => $statusLelang,
                'keterangan_options' => $keteranganOptions,
            ],
        ]);
    }

    public function update3DCase(Request $request, $formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) {
            $cases = json_decode($cases, true);
        }

        $validated = $request->validate([
            'satuanKerja'              => 'nullable|string',
            'terpidana_nama'         => 'required|string',
            'tgl_penyerahan'         => 'required|string',
            'putusan_no'             => 'required|string',
            'putusan_tgl'            => 'required|string',
            'perkara'                => 'required|string',
            'items'                  => 'required|array|min:1',
            'items.*.nama_barang'      => 'required|string',
            'items.*.harga_taksiran'   => 'nullable|numeric',
            'items.*.instansi_penilai' => 'nullable|string',
            'items.*.tgl_penilaian'    => 'nullable|string',
            'items.*.nilai_laku'       => 'nullable|numeric',
            'items.*.status_lelang'    => 'nullable|string',
            'items.*.keterangan'       => 'nullable|string',
        ]);

        $cases[$index] = $validated;
        $form->update(['cases' => array_values($cases)]);

        return redirect()->route('form3d.index')->with('success', 'Kasus Form 3D berhasil diperbarui.');
    }

    public function destroy3DCase($formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) {
            $cases = json_decode($cases, true);
        }

        if (isset($cases[$index])) {
            array_splice($cases, $index, 1);
            $form->update(['cases' => array_values($cases)]);
        }

        return back()->with('success', 'Kasus Form 3D berhasil dihapus.');
    }

    // ==========================================
    // FORM 3E (Lelang Barang Rampasan Negara)
    // ==========================================
    public function index3E()
    {
        $forms = FormTemplate::where('form_type', '3E')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return Inertia::render('Tabs/Form3E', ['forms' => $forms]);
    }

    public function store3EForm(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:2100',
        ]);

        $exists = FormTemplate::where('form_type', '3E')
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($exists) {
            return back()->with('error', 'Form 3E untuk periode tersebut sudah ada.');
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $monthNames[(int)$request->month] ?? '';

        FormTemplate::create([
            'form_type' => '3E',
            'name'      => "FORM 3E {$namaBulan} {$request->year}",
            'month'     => $request->month,
            'year'      => $request->year,
            'cases'     => [],
        ]);

        return back()->with('success', 'Form Induk 3E berhasil dibuat.');
    }

    public function destroy3EForm($formId)
    {
        $form = FormTemplate::findOrFail($formId);
        $form->delete();
        return back()->with('success', 'Form 3E berhasil dihapus.');
    }

    public function create3ECase($formId)
    {
        $form = FormTemplate::findOrFail($formId);
        $keteranganOptions = DropdownOption::where('category', 'keterangan_3e')->pluck('label')->unique()->values()->toArray();
        
        return Inertia::render('Tabs/Form3EInput', [
            'formId' => $form->id,
            'month'  => $form->month,
            'year'   => $form->year,
            'dropdownOptions' => [
                'keterangan_options' => $keteranganOptions
                ],
        ]);
    }

    public function store3ECase(Request $request, $formId)
    {
        $form = FormTemplate::findOrFail($formId);
        $validated = $request->validate([
            'satuanKerja'    => 'nullable|string',
            'terpidana_nama' => 'required|string',
            'putusan_no'     => 'required|string',
            'putusan_tgl'    => 'required|string',
            'items'          => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string',
            'items.*.jumlah'      => 'nullable|numeric',
            'items.*.satuan'      => 'nullable|string',
            'items.*.keterangan'  => 'nullable|string',
            'items.*.harga_jual'   => 'required|numeric',    
            'tgl_penjualan'  => 'required|string',
            'ntpn'           => 'nullable|string',
            'keterangan'     => 'nullable|string',
        ]);

        $cases = $form->cases ?? [];
        if (is_string($cases)) $cases = json_decode($cases, true);

        $cases[] = $validated;
        $form->update(['cases' => $cases]);

        return redirect()->route('form3e.index')->with('success', 'Kasus Form 3E berhasil ditambahkan.');
    }

    public function edit3ECase($formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) $cases = json_decode($cases, true);

        if (!isset($cases[$index])) {
            return redirect()->route('form3e.index')->with('error', 'Kasus tidak ditemukan.');
        }

        $keteranganOptions = DropdownOption::where('category', 'keterangan_3e')->pluck('label')->unique()->values()->toArray();

        return Inertia::render('Tabs/Form3EInput', [
            'formId'    => $form->id,
            'caseIndex' => (int) $index,
            'caseData'  => $cases[$index],
            'month'     => $form->month,
            'year'      => $form->year,
            'dropdownOptions' => [
                'keterangan_options' => DropdownOption::where('category', 'keterangan_3e')->pluck('label')->unique()->values()->toArray()
            ]
        ]);
    }

    public function update3ECase(Request $request, $formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) $cases = json_decode($cases, true);

        $validated = $request->validate([
            'satuanKerja'    => 'nullable|string',
            'terpidana_nama' => 'required|string',
            'putusan_no'     => 'required|string',
            'putusan_tgl'    => 'required|string',
            'items'          => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string',
            'items.*.jumlah'      => 'nullable|numeric',
            'items.*.satuan'      => 'nullable|string',
            'items.*.keterangan'  => 'nullable|string',
            'items.*.harga_jual'   => 'required|numeric',
            'tgl_penjualan'  => 'required|string',
            'ntpn'           => 'nullable|string',
            'keterangan'     => 'nullable|string',
        ]);

        $cases[$index] = $validated;
        $form->update(['cases' => array_values($cases)]);

        return redirect()->route('form3e.index')->with('success', 'Kasus Form 3E berhasil diperbarui.');
    }

    public function destroy3ECase($formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) $cases = json_decode($cases, true);

        if (isset($cases[$index])) {
            array_splice($cases, $index, 1);
            $form->update(['cases' => array_values($cases)]);
        }

        return back()->with('success', 'Kasus Form 3E berhasil dihapus.');
    }

    // ==========================================
    // FORM 3F (Penjualan Langsung Barang Rampasan)
    // ==========================================
    public function index3F()
    {
        $forms = FormTemplate::where('form_type', '3F')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return Inertia::render('Tabs/Form3F', ['forms' => $forms]);
    }

    public function store3FForm(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2000|max:2100',
        ]);

        $exists = FormTemplate::where('form_type', '3F')
            ->where('month', $request->month)
            ->where('year', $request->year)
            ->first();

        if ($exists) {
            return back()->with('error', 'Form 3F untuk periode tersebut sudah ada.');
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $monthNames[(int)$request->month] ?? '';

        FormTemplate::create([
            'form_type' => '3F',
            'name'      => "FORM 3F {$namaBulan} {$request->year}",
            'month'     => $request->month,
            'year'      => $request->year,
            'cases'     => [],
        ]);

        return back()->with('success', 'Form Induk 3F berhasil dibuat.');
    }

    public function destroy3FForm($formId)
    {
        $form = FormTemplate::findOrFail($formId);
        $form->delete();
        return back()->with('success', 'Form 3F berhasil dihapus.');
    }

    public function create3FCase($formId)
    {
        $form = FormTemplate::findOrFail($formId);
        $keteranganOptions = DropdownOption::where('category', 'keterangan_3f')->pluck('label')->unique()->values()->toArray();

        return Inertia::render('Tabs/Form3FInput', [
            'formId' => $form->id,
            'month'  => $form->month,
            'year'   => $form->year,
            'dropdownOptions' => [
                'keterangan_options' => $keteranganOptions
            ]
        ]);
    }

    public function store3FCase(Request $request, $formId)
    {
        $form = FormTemplate::findOrFail($formId);
        $validated = $request->validate([
            'satuanKerja'    => 'nullable|string',
            'terpidana_nama' => 'required|string',
            'putusan_no'     => 'required|string',
            'putusan_tgl'    => 'required|string',
            'items'          => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string',
            'items.*.jumlah'      => 'nullable|numeric',
            'items.*.satuan'      => 'nullable|string',
            'items.*.keterangan'  => 'nullable|string', 
            'items.*.harga_jual'   => 'required|numeric',
            'tgl_penjualan'  => 'required|string',
            'ntpn'           => 'nullable|string',
            'keterangan'     => 'nullable|string',
        ]);

        $cases = $form->cases ?? [];
        if (is_string($cases)) $cases = json_decode($cases, true);

        $cases[] = $validated;
        $form->update(['cases' => $cases]);

        return redirect()->route('form3f.index')->with('success', 'Kasus Form 3F berhasil ditambahkan.');
    }

    public function edit3FCase($formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) $cases = json_decode($cases, true);

        if (!isset($cases[$index])) {
            return redirect()->route('form3f.index')->with('error', 'Kasus tidak ditemukan.');
        }

        $keteranganOptions = DropdownOption::where('category', 'keterangan_3f')->pluck('label')->unique()->values()->toArray();

        return Inertia::render('Tabs/Form3FInput', [
            'formId'    => $form->id,
            'caseIndex' => (int) $index,
            'caseData'  => $cases[$index],
            'month'     => $form->month,
            'year'      => $form->year,
            'dropdownOptions' => [
                'keterangan_options' => DropdownOption::where('category', 'keterangan_3f')->pluck('label')->unique()->values()->toArray()
            ]
        ]);
    }

    public function update3FCase(Request $request, $formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) $cases = json_decode($cases, true);

        $validated = $request->validate([
            'satuanKerja'    => 'nullable|string',
            'terpidana_nama' => 'required|string',
            'putusan_no'     => 'required|string',
            'putusan_tgl'    => 'required|string',
            'items'          => 'required|array|min:1',
            'items.*.nama_barang' => 'required|string',
            'items.*.jumlah'      => 'nullable|numeric',
            'items.*.satuan'      => 'nullable|string',
            'items.*.keterangan'  => 'nullable|string',
            'items.*.harga_jual'   => 'required|numeric',
            'tgl_penjualan'  => 'required|string',
            'ntpn'           => 'nullable|string',
            'keterangan'     => 'nullable|string',
        ]);

        $cases[$index] = $validated;
        $form->update(['cases' => array_values($cases)]);

        return redirect()->route('form3f.index')->with('success', 'Kasus Form 3F berhasil diperbarui.');
    }

    public function destroy3FCase($formId, $index)
    {
        $form = FormTemplate::findOrFail($formId);
        $cases = $form->cases ?? [];
        if (is_string($cases)) $cases = json_decode($cases, true);

        if (isset($cases[$index])) {
            array_splice($cases, $index, 1);
            $form->update(['cases' => array_values($cases)]);
        }

        return back()->with('success', 'Kasus Form 3F berhasil dihapus.');
    }

    private function getDropdownOptionsForForm(string $formType): array
    {
        return DropdownOption::whereIn('form_target', [$formType, 'Keduanya'])
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->pluck('label')->unique()->values()->all())
            ->toArray();
    }

    private function getFormsByType(string $type): array
    {
        return FormTemplate::where('form_type', $this->normalizeFormType($type))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (FormTemplate $form) => $this->mapForm($form))
            ->values()
            ->all();
    }

    private function findForm(string $type, string $id): FormTemplate
    {
        return FormTemplate::where('form_type', $this->normalizeFormType($type))
            ->findOrFail($id);
    }

    private function mapForm(FormTemplate $form): array
    {
        return [
            'id' => (string) $form->id,
            'name' => $form->name,
            'month' => (int) $form->month,
            'year' => (int) $form->year,
            'formType' => $form->form_type,
            'latestCase' => $this->mapLatestCase($form),
            'cases' => $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []),
        ];
    }

    private function mapLatestCase(FormTemplate $form): ?array
    {
        if (!$form->latest_case_summary || !is_array($form->latest_case_summary)) {
            return null;
        }

        $summary = $form->latest_case_summary;
        $bbList = $summary['barangBuktiList'] ?? [];

        $formattedBbList = array_map(function ($bb) {
            $jumlahInput = $bb['jumlah'] ?? $bb['jumlahSatuan'] ?? 0;
            $satuanInput = $bb['satuanNarkotika'] ?? $bb['satuan'] ?? $bb['jenisSatuan'] ?? '-';

            return array_merge($bb, [
                'jumlah' => (float) $jumlahInput,
                'satuan' => $satuanInput,
                'jumlahNarkotika' => isset($bb['jumlahNarkotika']) ? (float) $bb['jumlahNarkotika'] : null,
                'satuanNarkotika' => $bb['satuanNarkotika'] ?? null,
            ]);
        }, $bbList);

        $totalJumlahUnit = array_reduce($formattedBbList, function ($carry, $bb) {
            return $carry + ($bb['jumlah'] ?? 0);
        }, 0);

        return [
            'satuanKerja' => $summary['satuanKerja'] ?? '-',
            'kategoriTindakPidana' => $summary['kategoriTindakPidana'] ?? '-',
            'jenisBarangBukti' => $formattedBbList[0]['jenisBarangBukti'] ?? $formattedBbList[0]['uraianBarangBukti'] ?? '-',
            'noRegBendaSitaan' => $summary['noRegBendaSitaan'] ?? '-',
            'noRegPenyidikan' => $summary['noRegPenyidikan'] ?? '-',
            'jumlah' => $totalJumlahUnit > 0 ? $totalJumlahUnit : ($summary['jumlah'] ?? 0),
            'satuan' => $formattedBbList[0]['satuan'] ?? '-',
            'statusDiselesaikan' => $summary['statusDiselesaikan'] ?? $formattedBbList[0]['amarPutusan'] ?? '-',
            'amarPutusan' => $formattedBbList[0]['amarPutusan'] ?? '-',
            'barangBuktiList' => $formattedBbList,
            'savedAt' => $this->formatSavedAt($form->latest_case_saved_at),
        ];
    }

    private function formatSavedAt(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d-m-Y H:i');
        }

        return null;
    }

    private function normalizeFormType(string $type): string
    {
        return strtoupper($type);
    }

    private function calculateSisaBulanLalu(int $targetMonth, int $targetYear, string $kategori): int
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
                $masuk = $this->countCasesFromForm('3A', $curM, $curY, $kategori);
                $selesai = $this->countCasesFromForm('3C', $curM, $curY, $kategori);

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

    private function countCasesFromForm(string $formType, int $month, int $year, string $kategori): int
    {
        $forms = FormTemplate::where('form_type', $formType)
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $count = 0;
        foreach ($forms as $form) {
            $cases = $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []);
            foreach ($cases as $c) {
                if ($this->matchCategory($c['kategoriTindakPidana'] ?? '', $kategori)) {
                    $count++;
                }
            }
        }

        return $count;
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
}