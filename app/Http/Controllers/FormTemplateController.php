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

    public function store(Request $request, string $type)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        FormTemplate::create([
            'form_type' => $this->normalizeFormType($type),
            'name' => $validated['name'],
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
        ]);

        return redirect()->back();
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
            'kejaksaan' => $form->latest_case_summary['kejaksaan'] ?? '-',
            'kategoriTindakPidana' => $form->latest_case_summary['kategoriTindakPidana'] ?? '-',
            'jenisBarangBukti' => $form->latest_case_summary['jenisBarangBukti'] ?? '-',
            'noRegBendaSitaan' => $form->latest_case_summary['noRegBendaSitaan'] ?? '-',
            'jumlahSatuan' => $form->latest_case_summary['jumlahSatuan'] ?? '-',
            'jenisSatuan' => $form->latest_case_summary['jenisSatuan'] ?? '-',
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
