<template>
  <Head title="Form 3B - Penanganan Perkara" />
  
  <SidebarLayout>
    <div class="p-8 space-y-6">
      
      <!-- Top Action & Filter Bar -->
      <div class="bg-white rounded-xl p-6 shadow-xs border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2">
            <FileText class="w-5 h-5 text-slate-700" />
            <h1 class="text-base font-extrabold text-slate-900 tracking-wide">
              DATA CASE FORM 3B
            </h1>
          </div>
          <p class="text-xs text-gray-400 font-medium mt-1">
            Rekapitulasi Penanganan Perkara & Barang Bukti
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <!-- FILTER BARU: Kategori Tindak Pidana -->
          <div class="flex items-center gap-2 bg-[#FFFBEB] border border-amber-200 rounded-lg px-3 py-1.5">
            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">KATEGORI</span>
            <select v-model="selectedKategori" class="bg-transparent text-xs font-bold text-slate-900 focus:outline-none cursor-pointer">
              <option v-for="kat in kategoriPidanaOptions" :key="kat" :value="kat">
                {{ kat }}
              </option>
            </select>
          </div>

          <!-- Filter Periode -->
          <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">PERIODE</span>
            <select v-model="selectedPeriod" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer">
              <option value="JULI 2026">JULI 2026</option>
              <option value="AGUSTUS 2026">AGUSTUS 2026</option>
            </select>
          </div>

          <!-- Buttons -->
          <button type="button" class="p-2 border border-gray-200 hover:bg-gray-50 rounded-lg text-gray-600 transition-colors flex items-center gap-1.5 text-xs font-bold cursor-pointer">
            <RefreshCw class="w-3.5 h-3.5" />
            <span>SINKRONKAN DATA</span>
          </button>
          <button type="button" class="bg-[#FFD000] hover:bg-yellow-400 text-slate-900 text-xs font-extrabold px-3.5 py-2 rounded-lg flex items-center gap-1.5 transition-colors cursor-pointer">
            <Download class="w-3.5 h-3.5" />
            <span>EXPORT PDF 3B OFFICIAL</span>
          </button>
        </div>
      </div>

      <!-- Main Form Table Card (Tabel tetap sama seperti sebelumnya) -->
      <div class="bg-white rounded-xl shadow-xs border border-gray-100 p-6">
        <form @submit.prevent="saveForm">
          <!-- ... (KODE TABEL 3B TETAP SAMA) ... -->
        </form>
      </div>

    </div>
  </SidebarLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import SidebarLayout from '@/Layouts/Layout.vue';
import { FileText, RefreshCw, Download, Plus, Trash2, Save } from 'lucide-vue-next';

// State Filter Baru
const selectedPeriod = ref('JULI 2026');
const selectedKategori = ref('NARKOTIKA DAN ZAT ADITIF LAINNYA'); // Default state

// Options Kategori Tindak Pidana
const kategoriPidanaOptions = [
  'KAMNEGTIBUM DAN TPUL',
  'NARKOTIKA DAN ZAT ADITIF LAINNYA',
  'OHARDA',
  'TERORIS',
  'KORUPSI'
];

// Dynamic Form Rows State (Tetap sama)
const formRows = ref([
  // ... data list
]);

// ... (Fungsi addRow dan removeRow tetap sama)

// UPDATE Fungsi saveForm untuk menyertakan kategori
const saveForm = () => {
  const payload = {
    period: selectedPeriod.value,
    kategori: selectedKategori.value, // <--- Penambahan payload kategori
    reports: formRows.value.map(row => ({
      ...row,
      jumlahBulan: row.sisaLalu + row.masukBulan
    }))
  };

  console.log('Submitted Payload:', payload);
  alert(`Data Form 3B (${selectedKategori.value}) Berhasil Disimpan!`);
};
</script>