<?php

namespace App\Http\Controllers;

use App\Models\DropdownOption;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DropdownOptionController extends Controller
{

    public function index()
    {
        $optionsData = DropdownOption::orderBy('id', 'asc')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'category' => $item->category,
                'label' => $item->label,
                'formTarget' => $item->form_target,
            ]);

        return Inertia::render('Tabs/PengaturanForm', [
            'optionsData' => $optionsData,
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
}