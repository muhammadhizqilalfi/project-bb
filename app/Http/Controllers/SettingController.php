<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index(){
        $options = DropdownOption::orderBy('category')
            ->orderBy('label')
            ->get();

        $optionsData = $options->map(function ($item) {
            return [
                'id' => $item->id,
                'category' => $item->category,
                'label' => $item->label,
                'formTarget' => $item->form_target,
            ];
        });

        $officerSetting = Setting::where('key', 'pejabat_kasi')->first();

        $officerData = $officerSetting?->value ?? [
            'jabatan_kasi' => '',
            'nama_kasi' => '',
            'nip_kasi' => '',
            'pangkat_kasi' => '',
        ];

        return Inertia::render('Tabs/PengaturanForm', [
            'optionsData' => $optionsData,
            'officerData' => $officerData,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'formTarget' => 'required|in:3A,3C,Keduanya',
        ]);

        DropdownOption::create([
            'category' => $validated['category'],
            'label' => $validated['label'],
            'form_target' => $validated['formTarget'],
        ]);

        return redirect()->back()->with('success', 'Opsi berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'formTarget' => 'required|in:3A,3C,Keduanya',
        ]);

        $option = DropdownOption::findOrFail($id);
        $option->update([
            'category' => $validated['category'],
            'label' => $validated['label'],
            'form_target' => $validated['formTarget'],
        ]);

        return redirect()->back()->with('success', 'Opsi berhasil diperbarui');
    }

    public function destroy($id)
    {
        $option = DropdownOption::findOrFail($id);
        $option->delete();

        return redirect()->back()->with('success', 'Opsi berhasil dihapus');
    }

    public function officer()
    {
        $setting = Setting::where('key', 'pejabat_kasi')->first();

        return response()->json([
            'officerData' => $setting?->value ?? [
                'jabatan_kasi' => '',
                'nama_kasi' => '',
                'nip_kasi' => '',
                'pangkat_kasi' => '',
            ],
        ]);
    }

    public function saveOfficer(Request $request)
    {
        $validated = $request->validate([
            'jabatan_kasi' => ['required', 'string', 'max:255'],
            'nama_kasi' => ['required', 'string', 'max:255'],
            'nip_kasi' => ['required', 'string', 'max:100'],
            'pangkat_kasi' => ['nullable', 'string', 'max:255'],
        ]);

        Setting::updateOrCreate(
            ['key' => 'pejabat_kasi'],
            ['value' => $validated]
        );

        return back()->with(
            'success',
            'Data Penandatangan Laporan berhasil diperbarui.'
        );
    }
}