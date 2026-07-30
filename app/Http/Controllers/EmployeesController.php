<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class EmployeesController extends Controller
{
    public function index()
    {
        $sortOrder = request()->query('sort', 'latest');

        $sortColumn = 'created_at';
        $direction = 'desc';

        if ($sortOrder === 'oldest') {
            $sortColumn = 'created_at';
            $direction = 'asc';
        } elseif ($sortOrder === 'az') {
            $sortColumn = 'name';
            $direction = 'asc';
        } elseif ($sortOrder === 'za') {
            $sortColumn = 'name';
            $direction = 'desc';
        }

        // 1. Ambil data dari MongoDB dengan pagination 6 data per halaman
        $employees = User::orderBy($sortColumn, $direction)->paginate(6);

        $employees->setCollection($employees->getCollection()->map(function ($user) {
            return [
                'id'   => (string) $user->_id, // Konversi ObjectId MongoDB ke String
                'name' => $user->name,
                'nip'  => $user->nip,
            ];
        }));

        // 2. Hitung statistik (karena tidak ada kolom 'status', activeStaff disamakan dengan total)
        $totalCount = User::count();

        return Inertia::render('Tabs/PengaturanAkun', [ // Pastikan nama komponen Vue sesuai
            'employees' => $employees,
            'stats'     => [
                'totalStaff'  => $totalCount,
                'activeStaff' => $totalCount,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|string|unique:users,nip',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            'name'     => $request->name,
            'nip'      => $request->nip,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nip'      => 'required|string|unique:users,nip,' . $id . ',_id',
            'password' => 'nullable|string|min:6',
        ]);

        $user = User::findOrFail($id);
        
        $data = [
            'name' => $request->name,
            'nip'  => $request->nip,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back();
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back();
    }
}