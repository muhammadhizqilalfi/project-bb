<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
  Gavel,
  Package,
  CheckSquare,
  Eye,
  Save
} from 'lucide-vue-next';
import { ref } from 'vue';
import type { PropType } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';

const activeMenu = ref('FORM');

type FormItem = {
  id: string;
  name: string;
  month: number;
  year: number;
};

defineProps({
  form: {
    type: Object as PropType<FormItem>,
    required: true
  }
});

const formCase = ref({
  satuanKerja: '',
  kategoriTindakPidana: '',
  noRegBendaSitaan: '',
  noRegPenyidikan: '',
  identitasTersangka: '',
  pasalDisangkakan: '',
  jenisBarangBukti: '',
  namaBarangBukti: '',
  jumlah: '',
  satuan: '',
  ukuranDetail: '',
  tempatPenyimpanan: '',
  statusDiselesaikan: 'Belum Selesai',
  tglPelaksanaanPutusan: '',
  keterangan: ''
});

const kategoriPidanaOptions = [
  'KAMNEGTIBUM DAN TPUL',
  'NARKOTIKA DAN ZAT ADITIF LAINNYA',
  'OHARDA',
  'TERORIS',
  'KORUPSI'
];

const submitForm = () => {
  console.log('Data Form 3A disubmit:', formCase.value);
};
</script>

<template>
  <Head title="Input Form 3A - LapBul" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 space-y-6 max-w-7xl mx-auto">
      <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-2">
        <p class="text-xs text-amber-900">
          Mengisi case untuk:
          <span class="font-bold">{{ form.name }}</span>
        </p>
      </div>

      <div class="flex items-center justify-between border-b border-gray-200 pb-4">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            Input Data Case Form 3A
          </h1>
          <p class="text-xs text-slate-500 mt-1">
            Laporan Bulanan Benda Sitaan & Barang Bukti Berdasarkan Kategori Tindak Pidana
          </p>
        </div>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          <div class="lg:col-span-6 bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
              <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
                <Gavel class="w-5 h-5" />
              </div>
              <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                IDENTITAS PERKARA & SATKER
              </h2>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                SATUAN KERJA <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formCase.satuanKerja"
                required
                class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
              >
                <option value="" disabled>Pilih Satuan Kerja...</option>
                <option value="Kejaksaan Tinggi Aceh">Kejaksaan Tinggi Aceh</option>
                <option value="Kejaksaan Negeri Banda Aceh">Kejaksaan Negeri Banda Aceh</option>
              </select>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                KATEGORI TINDAK PIDANA <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formCase.kategoriTindakPidana"
                required
                class="w-full bg-[#FFFBEB] border border-amber-300 rounded-lg px-3.5 py-2.5 text-xs font-bold text-slate-900 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000] transition-all"
              >
                <option value="" disabled>-- Pilih Jenis Tindak Pidana --</option>
                <option v-for="kat in kategoriPidanaOptions" :key="kat" :value="kat">
                  {{ kat }}
                </option>
              </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  NO. REG BENDA SITAAN <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formCase.noRegBendaSitaan"
                  type="text"
                  required
                  placeholder="B-000/O.1.10/..."
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  NO. REG PENYIDIKAN <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formCase.noRegPenyidikan"
                  type="text"
                  required
                  placeholder="PRINT-00/O.1.10/..."
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                IDENTITAS TERSANGKA / TERDAKWA <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="formCase.identitasTersangka"
                rows="3"
                required
                placeholder="Nama lengkap, alias, NIK, umur, pekerjaan..."
                class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"
              ></textarea>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                PASAL YANG DISANGKAKAN / DIDAKWAKAN <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formCase.pasalDisangkakan"
                type="text"
                required
                placeholder="Contoh: Pasal 114 ayat (1) UU No. 35 Tahun 2009"
                class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
              />
            </div>
          </div>

          <div class="lg:col-span-6 space-y-6">
            <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5">
              <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
                    <Package class="w-5 h-5" />
                  </div>
                  <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                    URAIAN BARANG BUKTI & PENYIMPANAN
                  </h2>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    JENIS BARANG BUKTI <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formCase.jenisBarangBukti"
                    type="text"
                    required
                    placeholder="e.g. Narkotika / Kendaraan"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    TEMPAT PENYIMPANAN <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formCase.tempatPenyimpanan"
                    type="text"
                    required
                    placeholder="Gudang BB Locker A2"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    JUMLAH <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formCase.jumlah"
                    type="number"
                    required
                    placeholder="0"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    SATUAN <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formCase.satuan"
                    type="text"
                    required
                    placeholder="Gram / Unit / Paket"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  UKURAN / DETAIL URAIAN BARANG <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="formCase.ukuranDetail"
                  rows="2"
                  required
                  placeholder="Contoh: Plastik klip transparan berisi kristal putih, berat bruto 10 gram..."
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"
                ></textarea>
              </div>
            </div>

            <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5">
              <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
                  <CheckSquare class="w-5 h-5" />
                </div>
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                  STATUS & PUTUSAN
                </h2>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    DISELESAIKAN
                  </label>
                  <select
                    v-model="formCase.statusDiselesaikan"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  >
                    <option value="Belum Selesai">Belum Selesai (Dalam Proses)</option>
                    <option value="Dikembalikan">Dikembalikan kepada yang Berhak</option>
                    <option value="Dirampas Negara">Dirampas untuk Negara</option>
                    <option value="Dimusnahkan">Dimusnahkan</option>
                    <option value="Diserahkan">Diserahkan ke Instansi Lain</option>
                  </select>
                </div>

                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    TGL PUTUSAN & IJIN JAKSA AGUNG
                  </label>
                  <input
                    v-model="formCase.tglPelaksanaanPutusan"
                    type="date"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  KETERANGAN
                </label>
                <textarea
                  v-model="formCase.keterangan"
                  rows="2"
                  placeholder="Catatan proses, nomor putusan pengadilan, atau kendala eksekusi..."
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
          <button
            type="button"
            class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer"
          >
            <Eye class="w-4 h-4" />
            <span>PREVIEW FORM 3A</span>
          </button>
          <button
            type="submit"
            class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-6 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-sm cursor-pointer"
          >
            <Save class="w-4 h-4" />
            <span>SAVE & PROCEED TO LAPORAN PAGE</span>
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
