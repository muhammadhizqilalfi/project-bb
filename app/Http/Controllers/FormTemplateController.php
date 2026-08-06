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

    public function edit3C(string $id)
    {
        $form = $this->findForm('3C', $id);
        $summary = $form->latest_case_summary ?? [];

        return Inertia::render('Tabs/Form3CInput', [
            'form' => $this->mapForm($form),
            'caseData' => array_merge($summary, ['id' => (string) $form->id]),
            'dropdownOptions' => $this->getDropdownOptionsForForm('3C'),
        ]);
    }

    public function update3C(Request $request, string $id)
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

        $cases = $form->cases ?? [];
        if (!empty($cases)) {
            $cases[count($cases) - 1] = $validated;
        } else {
            $cases = [$validated];
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

    public function destroy3C(string $id)
    {
        $form = $this->findForm('3C', $id);
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

    private function getDropdownOptionsForForm(string $formType): array
    {
        return DropdownOption::whereIn('form_target', [$formType, 'Both'])
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->pluck('label')->values()->all())
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
}