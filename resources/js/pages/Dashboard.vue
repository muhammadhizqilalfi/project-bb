<template>
    <Head title="Dashboard - Sistem Rekapitulasi Barang Bukti" />

    <div class="min-h-screen bg-gray-100">
        <!-- Header / Navbar -->
        <nav class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shadow-sm">
            <div class="flex items-center gap-3">
                <img src="/images/logo.png" alt="Logo" class="w-8 h-8 object-contain" />
                <span class="font-bold text-gray-800 text-lg">Kejaksaan RI — Rekap BB</span>
            </div>

            <div class="flex items-center gap-4">
                <!-- Menampilkan NIP / Nama User yang login -->
                <span class="text-sm text-gray-600 font-medium">
                    NIP: <strong class="text-gray-900">{{ $page.props.auth?.user?.nip || 'Pegawai' }}</strong>
                </span>

                <!-- Tombol Logout -->
                <button
                    @click="handleLogout"
                    class="bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold px-3 py-2 rounded-lg transition-colors cursor-pointer"
                >
                    Keluar
                </button>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-8 px-6">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Selamat Datang di Portal</h1>
                <p class="text-gray-600 text-sm">
                    Sistem Rekapitulasi Barang Bukti siap digunakan. Silakan pilih menu di bawah ini.
                </p>

                <!-- Ringkasan Statistik Dummy -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="bg-[#F8F9FA] p-4 rounded-lg border border-gray-100">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider block mb-1">Total Barang Bukti</span>
                        <span class="text-2xl font-bold text-gray-800">128</span>
                    </div>
                    <div class="bg-[#F8F9FA] p-4 rounded-lg border border-gray-100">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider block mb-1">Status Terverifikasi</span>
                        <span class="text-2xl font-bold text-emerald-600">94</span>
                    </div>
                    <div class="bg-[#F8F9FA] p-4 rounded-lg border border-gray-100">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider block mb-1">Menunggu Proses</span>
                        <span class="text-2xl font-bold text-amber-500">34</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';

const handleLogout = () => {
    router.post('/logout');
};
</script>