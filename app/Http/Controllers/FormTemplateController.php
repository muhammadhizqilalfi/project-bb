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

    // Method untuk menyimpan Form 3A + Case Pertama dari Wizard (MULTI BARANG BUKTI)
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
            'case.statusDiselesaikan' => 'required|string|max:255',
            'case.tglPelaksanaanPutusan' => 'nullable|date',
            'case.keterangan' => 'nullable|string',

            // Validasi Array Multi Barang Bukti Form 3A
            'case.barangBuktiList' => 'required|array|min:1',
            'case.barangBuktiList.*.jenisBarangBukti' => 'required|string|max:255',
            'case.barangBuktiList.*.namaBarangBukti' => 'required|string|max:255',
            'case.barangBuktiList.*.jumlah' => 'required|numeric',
            'case.barangBuktiList.*.satuan' => 'required|string|max:255',
            'case.barangBuktiList.*.ukuranDetail' => 'required|string',
            'case.barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
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
                'identitasTersangka' => $validated['case']['identitasTersangka'],
                'pasalDisangkakan' => $validated['case']['pasalDisangkakan'],
                'statusDiselesaikan' => $validated['case']['statusDiselesaikan'],
                'tglPelaksanaanPutusan' => $validated['case']['tglPelaksanaanPutusan'] ?? null,
                'keterangan' => $validated['case']['keterangan'] ?? null,
                'barangBuktiList' => $validated['case']['barangBuktiList'],
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

    // Method untuk menyimpan Form 3C + Case Pertama dari Wizard (MULTI BARANG BUKTI)
    public function store3CWizard(Request $request)
    {
        $validated = $request->validate([
            'header.name' => 'required|string|max:255',
            'header.month' => 'required|integer|min:1|max:12',
            'header.year' => 'required|integer|min:2000|max:2100',

            'case.kejaksaan' => 'required|string|max:255',
            'case.kategoriTindakPidana' => 'required|string|max:255',
            'case.pasalDidakwakan' => 'required|string|max:255',
            'case.noRegBendaSitaan' => 'required|string|max:255',
            'case.tglPenerimaan' => 'required|date',
            'case.noKepPengadilan' => 'required|string|max:255',
            'case.tglKepPengadilan' => 'required|date',
            'case.amarPutusan' => 'required|string',
            'case.tglPelaksanaanPutusan' => 'nullable|date',

            // Validasi Array Multi Barang Bukti Form 3C
            'case.barangBuktiList' => 'required|array|min:1',
            'case.barangBuktiList.*.jenisBarangBukti' => 'required|string|max:255',
            'case.barangBuktiList.*.macamJenisKadar' => 'required|string',
            'case.barangBuktiList.*.jumlahSatuan' => 'required|numeric',
            'case.barangBuktiList.*.jenisSatuan' => 'required|string|max:255',
            'case.barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
        ]);

        FormTemplate::create([
            'form_type' => '3C',
            'name' => $validated['header']['name'],
            'month' => (int) $validated['header']['month'],
            'year' => (int) $validated['header']['year'],
            'latest_case_summary' => [
                'kejaksaan' => $validated['case']['kejaksaan'],
                'kategoriTindakPidana' => $validated['case']['kategoriTindakPidana'],
                'pasalDidakwakan' => $validated['case']['pasalDidakwakan'],
                'noRegBendaSitaan' => $validated['case']['noRegBendaSitaan'],
                'tglPenerimaan' => $validated['case']['tglPenerimaan'],
                'noKepPengadilan' => $validated['case']['noKepPengadilan'],
                'tglKepPengadilan' => $validated['case']['tglKepPengadilan'],
                'amarPutusan' => $validated['case']['amarPutusan'],
                'tglPelaksanaanPutusan' => $validated['case']['tglPelaksanaanPutusan'] ?? null,
                'barangBuktiList' => $validated['case']['barangBuktiList'],
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

    // Method untuk menyimpan Case Baru pada Form 3A yang sudah ada (MULTI BARANG BUKTI)
    public function store3ACase(Request $request, string $id)
    {
        $validated = $request->validate([
            'satuanKerja' => 'required|string|max:255',
            'kategoriTindakPidana' => 'required|string|max:255',
            'noRegBendaSitaan' => 'required|string|max:255',
            'noRegPenyidikan' => 'required|string|max:255',
            'identitasTersangka' => 'required|string',
            'pasalDisangkakan' => 'required|string|max:255',
            'statusDiselesaikan' => 'required|string|max:255',
            'tglPelaksanaanPutusan' => 'nullable|date',
            'keterangan' => 'nullable|string',

            // Validasi Array Multi Barang Bukti Form 3A
            'barangBuktiList' => 'required|array|min:1',
            'barangBuktiList.*.jenisBarangBukti' => 'required|string|max:255',
            'barangBuktiList.*.namaBarangBukti' => 'required|string|max:255',
            'barangBuktiList.*.jumlah' => 'required|numeric',
            'barangBuktiList.*.satuan' => 'required|string|max:255',
            'barangBuktiList.*.ukuranDetail' => 'required|string',
            'barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
        ]);

        $form = $this->findForm('3A', $id);

        $form->update([
            'latest_case_summary' => [
                'satuanKerja' => $validated['satuanKerja'],
                'kategoriTindakPidana' => $validated['kategoriTindakPidana'],
                'noRegBendaSitaan' => $validated['noRegBendaSitaan'],
                'noRegPenyidikan' => $validated['noRegPenyidikan'],
                'identitasTersangka' => $validated['identitasTersangka'],
                'pasalDisangkakan' => $validated['pasalDisangkakan'],
                'statusDiselesaikan' => $validated['statusDiselesaikan'],
                'tglPelaksanaanPutusan' => $validated['tglPelaksanaanPutusan'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'barangBuktiList' => $validated['barangBuktiList'],
            ],
            'latest_case_saved_at' => now(),
        ]);

        return redirect("/form3a/{$id}/edit")->with('success', 'form saved successfully');
    }

    // Method untuk menyimpan Case Baru pada Form 3C yang sudah ada (MULTI BARANG BUKTI)
    public function store3CCase(Request $request, string $id)
    {
        $validated = $request->validate([
            'kejaksaan' => 'required|string|max:255',
            'kategoriTindakPidana' => 'required|string|max:255',
            'pasalDidakwakan' => 'required|string|max:255',
            'noRegBendaSitaan' => 'required|string|max:255',
            'tglPenerimaan' => 'required|date',
            'noKepPengadilan' => 'required|string|max:255',
            'tglKepPengadilan' => 'required|date',
            'amarPutusan' => 'required|string',
            'tglPelaksanaanPutusan' => 'nullable|date',

            // Validasi Array Multi Barang Bukti Form 3C
            'barangBuktiList' => 'required|array|min:1',
            'barangBuktiList.*.jenisBarangBukti' => 'required|string|max:255',
            'barangBuktiList.*.macamJenisKadar' => 'required|string',
            'barangBuktiList.*.jumlahSatuan' => 'required|numeric',
            'barangBuktiList.*.jenisSatuan' => 'required|string|max:255',
            'barangBuktiList.*.tempatPenyimpanan' => 'required|string|max:255',
        ]);

        $form = $this->findForm('3C', $id);

        $form->update([
            'latest_case_summary' => [
                'kejaksaan' => $validated['kejaksaan'],
                'kategoriTindakPidana' => $validated['kategoriTindakPidana'],
                'pasalDidakwakan' => $validated['pasalDidakwakan'],
                'noRegBendaSitaan' => $validated['noRegBendaSitaan'],
                'tglPenerimaan' => $validated['tglPenerimaan'],
                'noKepPengadilan' => $validated['noKepPengadilan'],
                'tglKepPengadilan' => $validated['tglKepPengadilan'],
                'amarPutusan' => $validated['amarPutusan'],
                'tglPelaksanaanPutusan' => $validated['tglPelaksanaanPutusan'] ?? null,
                'barangBuktiList' => $validated['barangBuktiList'],
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
            'id' => (string) $form->id,
            'name' => $form->name,
            'month' => (int) $form->month,
            'year' => (int) $form->year,
            'formType' => $form->form_type,
            'latestCase' => $this->mapLatestCase($form),

            // MENGIRIMKAN DAFTAR CASE YANG SUDAH DITAMBAHKAN SEBELUMNYA
            'cases' => $form->cases ?? ($form->latest_case_summary ? [$form->latest_case_summary] : []),
        ];
    }

    // Mapping ringkasan case terbaru dengan penanganan fallback (Mendukung Multi BB & Single BB Legacy)
    private function mapLatestCase(FormTemplate $form): ?array
    {
        if (!$form->latest_case_summary || !is_array($form->latest_case_summary)) {
            return null;
        }

        $summary = $form->latest_case_summary;
        $bbList = $summary['barangBuktiList'] ?? [];
        $firstBb = $bbList[0] ?? [];

        // Fallback Jenis Barang Bukti
        $jenisBarangBukti = $summary['jenisBarangBukti'] 
            ?? $firstBb['jenisBarangBukti'] 
            ?? '-';

        // Fallback Jumlah
        $jumlah = isset($summary['jumlah']) 
            ? $summary['jumlah'] 
            : (isset($firstBb['jumlah']) 
                ? (string)$firstBb['jumlah'] 
                : (isset($firstBb['jumlahSatuan']) 
                    ? (string)$firstBb['jumlahSatuan'] 
                    : ($summary['jumlahSatuan'] ?? '-')));

        // Fallback Satuan
        $satuan = $summary['satuan'] 
            ?? $firstBb['satuan'] 
            ?? $firstBb['jenisSatuan'] 
            ?? $summary['jenisSatuan'] 
            ?? '-';

        return [
            'satuanKerja' => $summary['satuanKerja'] ?? $summary['kejaksaan'] ?? '-',
            'kategoriTindakPidana' => $summary['kategoriTindakPidana'] ?? '-',
            'jenisBarangBukti' => $jenisBarangBukti,
            'noRegBendaSitaan' => $summary['noRegBendaSitaan'] ?? '-',
            'noRegPenyidikan' => $summary['noRegPenyidikan'] ?? '-',
            'jumlah' => $jumlah,
            'satuan' => $satuan,
            'statusDiselesaikan' => $summary['statusDiselesaikan'] ?? $summary['amarPutusan'] ?? '-',
            'amarPutusan' => $summary['amarPutusan'] ?? '-',
            'barangBuktiList' => $bbList,
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