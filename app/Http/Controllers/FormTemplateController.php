<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use MongoDB\BSON\UTCDateTime;

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

    // Method untuk membuka halaman Wizard Buat Form 3A dari nol (Tahap 1)
    public function create3AWizard()
    {
        return Inertia::render('Tabs/Form3AInput', [
            'form' => null,
        ]);
    }

    // Method untuk menyimpan Form 3A + Case Pertama dari Wizard
    public function store3AWizard(Request $request)
    {
        $validated = $request->validate([
            'header.name' => 'required|string|max:255',
            'header.month' => 'required|integer|min:1|max:12',
            'header.year' => 'required|integer|min:2000|max:2100',
            
            'case.satuanKerja' => 'required|string|max:255',
            'case.kategoriTindakPidana' => 'required|string|max:255',
            'case.noRegBendaSitaan' => 'required|string|max:255',
            'case.noRegPenyidikan' => 'required|string|max:255',
            'case.identitasTersangka' => 'required|string',
            'case.pasalDisangkakan' => 'required|string|max:255',
            'case.jenisBarangBukti' => 'required|string|max:255',
            'case.jumlah' => 'required|numeric',
            'case.satuan' => 'required|string|max:255',
            'case.ukuranDetail' => 'required|string',
            'case.tempatPenyimpanan' => 'required|string|max:255',
            'case.statusDiselesaikan' => 'required|string|max:255',
            'case.tglPelaksanaanPutusan' => 'nullable|date',
            'case.keterangan' => 'nullable|string',
        ]);

        FormTemplate::create([
            'form_type' => '3A',
            'name' => $validated['header']['name'],
            'month' => (int) $validated['header']['month'],
            'year' => (int) $validated['header']['year'],
            'latest_case_summary' => [
                'satuanKerja' => $validated['case']['satuanKerja'],
                'kategoriTindakPidana' => $validated['case']['kategoriTindakPidana'],
                'noRegBendaSitaan' => $validated['case']['noRegBendaSitaan'],
                'noRegPenyidikan' => $validated['case']['noRegPenyidikan'],
                'jenisBarangBukti' => $validated['case']['jenisBarangBukti'],
                'jumlah' => (string) $validated['case']['jumlah'],
                'satuan' => $validated['case']['satuan'],
                'statusDiselesaikan' => $validated['case']['statusDiselesaikan'],
            ],
            'latest_case_saved_at' => now(),
        ]);

        return redirect('/form3a')->with('success', 'Form dan case pertama berhasil disimpan');
    }

    // Method untuk membuka halaman Wizard Buat Form 3C dari nol (Tahap 1)
    public function create3CWizard()
    {
        return Inertia::render('Tabs/Form3CInput', [
            'form' => null,
        ]);
    }

    // Method untuk menyimpan Form 3C + Case Pertama dari Wizard
    public function store3CWizard(Request $request)
    {
        $validated = $request->validate([
            'header.name' => 'required|string|max:255',
            'header.month' => 'required|integer|min:1|max:12',
            'header.year' => 'required|integer|min:2000|max:2100',

            'case.kejaksaan' => 'required|string|max:255',
            'case.kategoriTindakPidana' => 'required|string|max:255',
            'case.jenisBarangBukti' => 'required|string|max:255',
            'case.pasalDidakwakan' => 'required|string|max:255',
            'case.noRegBendaSitaan' => 'required|string|max:255',
            'case.tglPenerimaan' => 'required|date',
            'case.macamJenisKadar' => 'required|string',
            'case.jumlahSatuan' => 'required|numeric',
            'case.jenisSatuan' => 'required|string|max:255',
            'case.tempatPenyimpanan' => 'required|string|max:255',
            'case.noKepPengadilan' => 'required|string|max:255',
            'case.tglKepPengadilan' => 'required|date',
            'case.amarPutusan' => 'required|string',
            'case.tglPelaksanaanPutusan' => 'nullable|date',
        ]);

        FormTemplate::create([
            'form_type' => '3C',
            'name' => $validated['header']['name'],
            'month' => (int) $validated['header']['month'],
            'year' => (int) $validated['header']['year'],
            'latest_case_summary' => [
                'kejaksaan' => $validated['case']['kejaksaan'],
                'kategoriTindakPidana' => $validated['case']['kategoriTindakPidana'],
                'jenisBarangBukti' => $validated['case']['jenisBarangBukti'],
                'noRegBendaSitaan' => $validated['case']['noRegBendaSitaan'],
                'jumlahSatuan' => (string) $validated['case']['jumlahSatuan'],
                'jenisSatuan' => $validated['case']['jenisSatuan'],
                'amarPutusan' => $validated['case']['amarPutusan'],
            ],
            'latest_case_saved_at' => now(),
        ]);

        return redirect('/form3c')->with('success', 'Form dan case pertama berhasil disimpan');
    }

    public function destroy(string $type, string $id)
    {
        $form = $this->findForm($type, $id);
        $form->delete();

        return redirect()->back();
    }

    public function edit3A(string $id)
    {
        return Inertia::render('Tabs/Form3AEdit', [
            'form' => $this->mapForm($this->findForm('3A', $id)),
        ]);
    }

    public function edit3C(string $id)
    {
        return Inertia::render('Tabs/Form3CEdit', [
            'form' => $this->mapForm($this->findForm('3C', $id)),
        ]);
    }

    public function create3ACase(string $id)
    {
        return Inertia::render('Tabs/Form3AInput', [
            'form' => $this->mapForm($this->findForm('3A', $id)),
        ]);
    }

    public function create3CCase(string $id)
    {
        return Inertia::render('Tabs/Form3CInput', [
            'form' => $this->mapForm($this->findForm('3C', $id)),
        ]);
    }

    public function store3ACase(Request $request, string $id)
    {
        $validated = $request->validate([
            'satuanKerja' => 'required|string|max:255',
            'kategoriTindakPidana' => 'required|string|max:255',
            'noRegBendaSitaan' => 'required|string|max:255',
            'noRegPenyidikan' => 'required|string|max:255',
            'identitasTersangka' => 'required|string',
            'pasalDisangkakan' => 'required|string|max:255',
            'jenisBarangBukti' => 'required|string|max:255',
            'namaBarangBukti' => 'required|string|max:255',
            'jumlah' => 'required|numeric',
            'satuan' => 'required|string|max:255',
            'ukuranDetail' => 'required|string',
            'tempatPenyimpanan' => 'required|string|max:255',
            'statusDiselesaikan' => 'required|string|max:255',
            'tglPelaksanaanPutusan' => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        $form = $this->findForm('3A', $id);

        $form->update([
            'latest_case_summary' => [
                'satuanKerja' => $validated['satuanKerja'],
                'kategoriTindakPidana' => $validated['kategoriTindakPidana'],
                'noRegBendaSitaan' => $validated['noRegBendaSitaan'],
                'noRegPenyidikan' => $validated['noRegPenyidikan'],
                'jenisBarangBukti' => $validated['jenisBarangBukti'],
                'jumlah' => (string) $validated['jumlah'],
                'satuan' => $validated['satuan'],
                'statusDiselesaikan' => $validated['statusDiselesaikan'],
            ],
            'latest_case_saved_at' => now(),
        ]);

        return redirect("/form3a/{$id}/edit")->with('success', 'form saved successfully');
    }

    public function store3CCase(Request $request, string $id)
    {
        $validated = $request->validate([
            'kejaksaan' => 'required|string|max:255',
            'kategoriTindakPidana' => 'required|string|max:255',
            'jenisBarangBukti' => 'required|string|max:255',
            'pasalDidakwakan' => 'required|string|max:255',
            'noRegBendaSitaan' => 'required|string|max:255',
            'tglPenerimaan' => 'required|date',
            'macamJenisKadar' => 'required|string',
            'jumlahSatuan' => 'required|numeric',
            'jenisSatuan' => 'required|string|max:255',
            'tempatPenyimpanan' => 'required|string|max:255',
            'noKepPengadilan' => 'required|string|max:255',
            'tglKepPengadilan' => 'required|date',
            'amarPutusan' => 'required|string',
            'tglPelaksanaanPutusan' => 'nullable|date',
        ]);

        $form = $this->findForm('3C', $id);

        $form->update([
            'latest_case_summary' => [
                'kejaksaan' => $validated['kejaksaan'],
                'kategoriTindakPidana' => $validated['kategoriTindakPidana'],
                'jenisBarangBukti' => $validated['jenisBarangBukti'],
                'noRegBendaSitaan' => $validated['noRegBendaSitaan'],
                'jumlahSatuan' => (string) $validated['jumlahSatuan'],
                'jenisSatuan' => $validated['jenisSatuan'],
                'amarPutusan' => $validated['amarPutusan'],
            ],
            'latest_case_saved_at' => now(),
        ]);

        return redirect("/form3c/{$id}/edit")->with('success', 'form saved successfully');
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
            'id' => (string) $form->_id,
            'name' => $form->name,
            'month' => (int) $form->month,
            'year' => (int) $form->year,
            'formType' => $form->form_type,
            'latestCase' => $this->mapLatestCase($form),
        ];
    }

    private function mapLatestCase(FormTemplate $form): ?array
    {
        if (!$form->latest_case_summary || !is_array($form->latest_case_summary)) {
            return null;
        }

        return [
            'satuanKerja' => $form->latest_case_summary['satuanKerja'] ?? $form->latest_case_summary['kejaksaan'] ?? '-',
            'kategoriTindakPidana' => $form->latest_case_summary['kategoriTindakPidana'] ?? '-',
            'jenisBarangBukti' => $form->latest_case_summary['jenisBarangBukti'] ?? '-',
            'noRegBendaSitaan' => $form->latest_case_summary['noRegBendaSitaan'] ?? '-',
            'noRegPenyidikan' => $form->latest_case_summary['noRegPenyidikan'] ?? '-',
            'jumlah' => $form->latest_case_summary['jumlah'] ?? $form->latest_case_summary['jumlahSatuan'] ?? '-',
            'satuan' => $form->latest_case_summary['satuan'] ?? $form->latest_case_summary['jenisSatuan'] ?? '-',
            'statusDiselesaikan' => $form->latest_case_summary['statusDiselesaikan'] ?? $form->latest_case_summary['amarPutusan'] ?? '-',
            'amarPutusan' => $form->latest_case_summary['amarPutusan'] ?? '-',
            'savedAt' => $this->formatSavedAt($form->latest_case_saved_at),
        ];
    }

    private function formatSavedAt(mixed $value): ?string
    {
        if ($value instanceof UTCDateTime) {
            return $value->toDateTime()->format('d-m-Y H:i');
        }

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