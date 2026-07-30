<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import {
  Gavel,
  Package,
  FileCheck2,
  Eye,
  Save,
  Calendar
} from 'lucide-vue-next';

const activeMenu = ref('FORM');

// Reactive Form State sesuai Header Form 3C
const form = ref({
  kejaksaan: '',
  kategoriTindakPidana: '', // Field Baru Kategori Tindak Pidana
  jenisBarangBukti: '',
  pasalDidakwakan: '',
  noRegBendaSitaan: '',
  tglPenerimaan: '',
  
  // Detail Uraian & Penyimpanan
  macamJenisKadar: '',
  jumlahSatuan: '',
  jenisSatuan: '',
  tempatPenyimpanan: '',
  
  // Putusan & Eksekusi Hakim
  noKepPengadilan: '',
  tglKepPengadilan: '',
  amarPutusan: '',
  tglPelaksanaanPutusan: ''
});

// Options Kategori Tindak Pidana LapBul
const kategoriPidanaOptions = [
  'KAMNEGTIBUM DAN TPUL',
  'NARKOTIKA DAN ZAT ADITIF LAINNYA',
  'OHARDA',
  'TERORIS',
  'KORUPSI'
];

const submitForm = () => {
  console.log('Data Form 3C disubmit:', form.value);
  // Logika simpan / Inertia router.post di sini
};
</script>

<template>
  <Head title="Input Form 3C - LapBul" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 space-y-6 max-w-7xl mx-auto">
      
      <!-- Header Halaman -->
      <div class="flex items-center justify-between border-b border-gray-200 pb-4">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            Input Data Case Form 3C
          </h1>
          <p class="text-xs text-slate-500 mt-1">
            Laporan Bulanan Barang Bukti Berdasarkan Putusan Pengadilan (PN / PT / MA)
          </p>
        </div>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          
          <!-- KOLOM KIRI: IDENTITAS PERKARA & REGISTER (6 Columns) -->
          <div class="lg:col-span-6 bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
              <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
                <Gavel class="w-5 h-5" />
              </div>
              <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                1. IDENTITAS PERKARA & REGISTER
              </h2>
            </div>

            <!-- Kejaksaan -->
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                KEJAKSAAN / SATKER <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.kejaksaan"
                required
                class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
              >
                <option value="" disabled>Pilih Kejaksaan Satuan Kerja...</option>
                <option value="Kejaksaan Tinggi Aceh">Kejaksaan Tinggi Aceh</option>
                <option value="Kejaksaan Negeri Banda Aceh">Kejaksaan Negeri Banda Aceh</option>
              </select>
            </div>

            <!-- Kategori Tindak Pidana -->
            <div>
              <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                KATEGORI TINDAK PIDANA <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.kategoriTindakPidana"
                required
                class="w-full bg-[#FFFBEB] border border-amber-300 rounded-lg px-3.5 py-2.5 text-xs font-bold text-slate-900 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000] transition-all"
              >
                <option value="" disabled>-- Pilih Jenis Tindak Pidana --</option>
                <option v-for="kat in kategoriPidanaOptions" :key="kat" :value="kat">
                  {{ kat }}
                </option>
              </select>
            </div>

            <!-- Jenis Barang Bukti & Pasal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  JENIS BARANG BUKTI <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.jenisBarangBukti"
                  type="text"
                  required
                  placeholder="e.g. Narkotika / Senjata Api"
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  PASAL YANG DIDAKWAKAN <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.pasalDidakwakan"
                  type="text"
                  required
                  placeholder="e.g. Pasal 112 ayat (1)"
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>

            <!-- Register BB Sitaan & Tanggal Penerimaan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  NO. REG BENDA SITAAN BB <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.noRegBendaSitaan"
                  type="text"
                  required
                  placeholder="RB-000/O.1.10/..."
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  TGL PENERIMAAN BB <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.tglPenerimaan"
                  type="date"
                  required
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>

            <!-- Macam Jenis Kadar -->
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                MACAM JENIS KADAR BARANG <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.macamJenisKadar"
                rows="3"
                required
                placeholder="Spesifikasi rinci, kadar kemurnian, merk, nomor seri, kondisi..."
                class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"
              ></textarea>
            </div>
          </div>

          <!-- KOLOM KANAN: PENYIMPANAN & PUTUSAN HAKIM (6 Columns) -->
          <div class="lg:col-span-6 space-y-6">
            
            <!-- CARD 2: JUMLAH & PENYIMPANAN -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5">
              <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
                  <Package class="w-5 h-5" />
                </div>
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                  2. JUMLAH & LOKASI PENYIMPANAN
                </h2>
              </div>

              <!-- Jumlah Satuan & Jenis Satuan -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    JUMLAH SATUAN <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.jumlahSatuan"
                    type="number"
                    required
                    placeholder="0"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    JENIS SATUAN <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.jenisSatuan"
                    type="text"
                    required
                    placeholder="e.g. Gram / Kg / Unit / Bungkus"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
              </div>

              <!-- Tempat Penyimpanan -->
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  TEMPAT PENYIMPANAN <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.tempatPenyimpanan"
                  type="text"
                  required
                  placeholder="Contoh: Gudang BB Locker A2 / Ruang Brankas"
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>

            <!-- CARD 3: PUTUSAN & PELAKSANAAN HAKIM -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5">
              <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
                  <FileCheck2 class="w-5 h-5" />
                </div>
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                  3. PUTUSAN & EKSEKUSI HAKIM (PN/PT/MA)
                </h2>
              </div>

              <!-- Tgl & No. KEP PN/PT/MA -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    NO. KEP (PN/PT/MA) <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.noKepPengadilan"
                    type="text"
                    required
                    placeholder="No. Putusan Pengadilan"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    TGL. KEP (PN/PT/MA) <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.tglKepPengadilan"
                    type="date"
                    required
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
              </div>

              <!-- Amar Putusan -->
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  AMAR PUTUSAN <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.amarPutusan"
                  rows="2"
                  required
                  placeholder="Contoh: Dirampas untuk dimusnahkan / Dikembalikan kepada saksi..."
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"
                ></textarea>
              </div>

              <!-- Tanggal Pelaksanaan Putusan Hakim -->
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  TANGGAL PELAKSANAAN PUTUSAN HAKIM
                </label>
                <input
                  v-model="form.tglPelaksanaanPutusan"
                  type="date"
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>

          </div>
        </div>

        <!-- Action Buttons Footer -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
          <button
            type="button"
            class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer"
          >
            <Eye class="w-4 h-4" />
            <span>PREVIEW FORM 3C</span>
          </button>
          <button
            type="submit"
            class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-6 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-sm cursor-pointer"
          >
            <Save class="w-4 h-4" />
            <span>SIMPAN DATA FORM 3C</span>
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>