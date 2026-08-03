<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
  Gavel,
  Package,
  FileCheck2,
  Eye,
  Save,
  RotateCcw,
  X
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

const props = defineProps({
  form: {
    type: Object as PropType<FormItem>,
    required: true
  }
});

const formCase = ref({
  kejaksaan: '',
  kategoriTindakPidana: '',
  jenisBarangBukti: '',
  pasalDidakwakan: '',
  noRegBendaSitaan: '',
  tglPenerimaan: '',
  macamJenisKadar: '',
  jumlahSatuan: '',
  jenisSatuan: '',
  tempatPenyimpanan: '',
  noKepPengadilan: '',
  tglKepPengadilan: '',
  amarPutusan: '',
  tglPelaksanaanPutusan: ''
});

const showPreviewModal = ref(false);

const kategoriPidanaOptions = [
  'KAMNEGTIBUM DAN TPUL',
  'NARKOTIKA DAN ZAT ADITIF LAINNYA',
  'OHARDA',
  'TERORIS',
  'KORUPSI'
];

const submitForm = () => {
  router.post(`/form3c/${props.form.id}/cases`, formCase.value);
};

const resetForm = () => {
  formCase.value = {
    kejaksaan: '',
    kategoriTindakPidana: '',
    jenisBarangBukti: '',
    pasalDidakwakan: '',
    noRegBendaSitaan: '',
    tglPenerimaan: '',
    macamJenisKadar: '',
    jumlahSatuan: '',
    jenisSatuan: '',
    tempatPenyimpanan: '',
    noKepPengadilan: '',
    tglKepPengadilan: '',
    amarPutusan: '',
    tglPelaksanaanPutusan: ''
  };
};
</script>

<template>
  <Head title="Input Form 3C - LapBul" />

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
            Input Data Case Form 3C
          </h1>
          <p class="text-xs text-slate-500 mt-1">
            Laporan Bulanan Barang Bukti Berdasarkan Putusan Pengadilan (PN / PT / MA)
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
                1. IDENTITAS PERKARA & REGISTER
              </h2>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                KEJAKSAAN / SATKER <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formCase.kejaksaan"
                required
                class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
              >
                <option value="" disabled>Pilih Kejaksaan Satuan Kerja...</option>
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
                  JENIS BARANG BUKTI <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formCase.jenisBarangBukti"
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
                  v-model="formCase.pasalDidakwakan"
                  type="text"
                  required
                  placeholder="e.g. Pasal 112 ayat (1)"
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  NO. REG BENDA SITAAN BB <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formCase.noRegBendaSitaan"
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
                  v-model="formCase.tglPenerimaan"
                  type="date"
                  required
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                MACAM JENIS KADAR BARANG <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="formCase.macamJenisKadar"
                rows="3"
                required
                placeholder="Spesifikasi rinci, kadar kemurnian, merk, nomor seri, kondisi..."
                class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"
              ></textarea>
            </div>
          </div>

          <div class="lg:col-span-6 space-y-6">
            <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5">
              <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
                  <Package class="w-5 h-5" />
                </div>
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                  2. JUMLAH & LOKASI PENYIMPANAN
                </h2>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    JUMLAH SATUAN <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formCase.jumlahSatuan"
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
                    v-model="formCase.jenisSatuan"
                    type="text"
                    required
                    placeholder="e.g. Gram / Kg / Unit / Bungkus"
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  TEMPAT PENYIMPANAN <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="formCase.tempatPenyimpanan"
                  type="text"
                  required
                  placeholder="Contoh: Gudang BB Locker A2 / Ruang Brankas"
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>

            <div class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5">
              <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
                  <FileCheck2 class="w-5 h-5" />
                </div>
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
                  3. PUTUSAN & EKSEKUSI HAKIM (PN/PT/MA)
                </h2>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                    NO. KEP (PN/PT/MA) <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="formCase.noKepPengadilan"
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
                    v-model="formCase.tglKepPengadilan"
                    type="date"
                    required
                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                  />
                </div>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  AMAR PUTUSAN <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="formCase.amarPutusan"
                  rows="2"
                  required
                  placeholder="Contoh: Dirampas untuk dimusnahkan / Dikembalikan kepada saksi..."
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"
                ></textarea>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                  TANGGAL PELAKSANAAN PUTUSAN HAKIM
                </label>
                <input
                  v-model="formCase.tglPelaksanaanPutusan"
                  type="date"
                  class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
          <button
            type="button"
            class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer"
            @click="showPreviewModal = true"
          >
            <Eye class="w-4 h-4" />
            <span>PREVIEW FORM</span>
          </button>
          <button
            type="button"
            class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-800 text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer"
            @click="resetForm"
          >
            <RotateCcw class="w-4 h-4" />
            <span>RESET FORM</span>
          </button>
          <button
            type="submit"
            class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-6 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-sm cursor-pointer"
          >
            <Save class="w-4 h-4" />
            <span>SAVE FORM</span>
          </button>
        </div>
      </form>
    </div>

    <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-xl rounded-xl bg-white border border-slate-200 shadow-xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <h2 class="text-sm font-bold text-slate-900">Preview Case Form 3C</h2>
          <button
            type="button"
            class="text-slate-500 hover:text-slate-700 cursor-pointer"
            @click="showPreviewModal = false"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
          <div><span class="text-slate-500">Kejaksaan:</span> <span class="font-medium">{{ formCase.kejaksaan || '-' }}</span></div>
          <div><span class="text-slate-500">Kategori:</span> <span class="font-medium">{{ formCase.kategoriTindakPidana || '-' }}</span></div>
          <div><span class="text-slate-500">Jenis BB:</span> <span class="font-medium">{{ formCase.jenisBarangBukti || '-' }}</span></div>
          <div><span class="text-slate-500">No Reg:</span> <span class="font-medium">{{ formCase.noRegBendaSitaan || '-' }}</span></div>
          <div><span class="text-slate-500">Jumlah:</span> <span class="font-medium">{{ formCase.jumlahSatuan || '-' }} {{ formCase.jenisSatuan || '' }}</span></div>
          <div><span class="text-slate-500">Putusan:</span> <span class="font-medium">{{ formCase.amarPutusan || '-' }}</span></div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
