<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
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