<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
  Gavel,
  Package,
  CheckSquare,
  Eye,
  Save,
  ArrowRight,
  ArrowLeft,
  Check,
  RotateCcw,
  X,
  Plus,
  Trash2
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import type { PropType } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';

const activeMenu = ref('FORM');

type BarangBukti = {
  jenisBarangBukti: string;
  namaBarangBukti: string;
  jumlah: string | number;
  satuan: string;
  ukuranDetail: string;
  tempatPenyimpanan: string;
};

type SavedCase = {
  satuanKerja?: string;
  kategoriTindakPidana?: string;
  noRegBendaSitaan?: string;
  noRegPenyidikan?: string;
  identitasTersangka?: string;
  pasalDisangkakan?: string;
  statusDiselesaikan?: string;
  tglPelaksanaanPutusan?: string;
  keterangan?: string;
  barangBuktiList?: BarangBukti[];
};

type FormItem = {
  id: string;
  name: string;
  month: number;
  year: number;
  cases?: SavedCase[];
};

const props = defineProps({
  form: {
    type: Object as PropType<FormItem | null>,
    default: null
  }
});

const isNewForm = computed(() => !props.form);
const currentStep = ref(props.form ? 2 : 1);

const formHeader = ref({
  name: props.form?.name || '',
  month: props.form?.month || new Date().getMonth() + 1,
  year: props.form?.year || new Date().getFullYear()
});

const createEmptyBarangBukti = (): BarangBukti => ({
  jenisBarangBukti: '',
  namaBarangBukti: '',
  jumlah: '',
  satuan: '',
  ukuranDetail: '',
  tempatPenyimpanan: ''
});

const formCase = ref({
  satuanKerja: '',
  kategoriTindakPidana: '',
  noRegBendaSitaan: '',
  noRegPenyidikan: '',
  identitasTersangka: '',
  pasalDisangkakan: '',
  statusDiselesaikan: 'Belum Selesai',
  tglPelaksanaanPutusan: '',
  keterangan: '',
  barangBuktiList: [createEmptyBarangBukti()]
});

const showPreviewModal = ref(false);

const monthOptions = [
  { value: 1, label: 'Januari' }, { value: 2, label: 'Februari' },
  { value: 3, label: 'Maret' }, { value: 4, label: 'April' },
  { value: 5, label: 'Mei' }, { value: 6, label: 'Juni' },
  { value: 7, label: 'Juli' }, { value: 8, label: 'Agustus' },
  { value: 9, label: 'September' }, { value: 10, label: 'Oktober' },
  { value: 11, label: 'November' }, { value: 12, label: 'Desember' }
];

const yearOptions = computed(() => {
  const currentYear = new Date().getFullYear();
  return Array.from({ length: 10 }, (_, index) => currentYear - index);
});

const kategoriPidanaOptions = [
  'KAMNEGTIBUM DAN TPUL',
  'NARKOTIKA DAN ZAT ADITIF LAINNYA',
  'OHARDA',
  'TERORIS',
  'KORUPSI'
];

const satuanOptions = [
  'Gram',
  'Kilogram (Kg)',
  'Milliliter (ml)',
  'Liter (L)',
  'Unit',
  'Paket',
  'Pcs',
  'Buah',
  'Bungkus',
  'Batang',
  'Lembar'
];

const addBarangBukti = () => {
  formCase.value.barangBuktiList.push(createEmptyBarangBukti());
};

const removeBarangBukti = (index: number) => {
  if (formCase.value.barangBuktiList.length > 1) {
    formCase.value.barangBuktiList.splice(index, 1);
  }
};

const goToStep2 = () => {
  if (!formHeader.value.name) {
    alert('Harap isi Nama Form terlebih dahulu.');
    return;
  }
  currentStep.value = 2;
};

const goToStep1 = () => {
  if (isNewForm.value) {
    currentStep.value = 1;
  }
};

const submitForm = () => {
  if (isNewForm.value) {
    router.post('/forms/3a/wizard', {
      header: formHeader.value,
      case: formCase.value
    });
  } else {
    router.post(`/form3a/${props.form?.id}/cases`, formCase.value);
  }
};

const resetCaseForm = () => {
  formCase.value = {
    satuanKerja: '',
    kategoriTindakPidana: '',
    noRegBendaSitaan: '',
    noRegPenyidikan: '',
    identitasTersangka: '',
    pasalDisangkakan: '',
    statusDiselesaikan: 'Belum Selesai',
    tglPelaksanaanPutusan: '',
    keterangan: '',
    barangBuktiList: [createEmptyBarangBukti()]
  };
};
</script>

<template>
  <Head :title="isNewForm ? 'Buat Form 3A Baru' : 'Tambah Case Form 3A'" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-8">
      
      <!-- PAGE HEADER -->
      <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            {{ isNewForm ? 'Buat Form 3A & Input Case' : 'Tambah Case Baru' }}
          </h1>
          <p class="text-xs text-slate-500 mt-1">
            Laporan Bulanan Benda Sitaan & Barang Bukti Berdasarkan Kategori Tindak Pidana
          </p>
        </div>
        
        <button
          type="button"
          class="text-xs font-bold text-slate-600 border border-slate-300 rounded-lg px-3.5 py-2 hover:bg-slate-100 flex items-center gap-1.5 cursor-pointer"
          @click="router.get('/form3a')"
        >
          <ArrowLeft class="w-4 h-4" />
          <span>Kembali ke Daftar</span>
        </button>
      </div>

      <!-- PROGRESS STEPPER BAR -->
      <div class="bg-slate-900 rounded-xl p-6 shadow-md border border-slate-800">
        <div class="flex items-center justify-center max-w-xl mx-auto">
          <div class="flex items-center gap-3 cursor-pointer" @click="goToStep1">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300', currentStep === 1 ? 'bg-slate-900 text-[#FFD000] border-2 border-[#FFD000] ring-4 ring-amber-500/20 shadow-[0_0_15px_rgba(255,208,0,0.4)]' : 'bg-emerald-500 text-white']">
              <Check v-if="currentStep > 1" class="w-5 h-5 stroke-[3]" />
              <span v-else>1</span>
            </div>
            <div class="text-left hidden sm:block">
              <p class="text-xs font-bold uppercase tracking-wider" :class="currentStep === 1 ? 'text-[#FFD000]' : 'text-slate-300'">Tahap 1</p>
              <p class="text-[11px] text-slate-400 font-medium">Informasi Form</p>
            </div>
          </div>

          <div class="flex-1 max-w-[120px] mx-4 h-0.5 transition-all duration-300" :class="currentStep >= 2 ? 'bg-emerald-500' : 'bg-slate-700'"></div>

          <div class="flex items-center gap-3">
            <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300', currentStep === 2 ? 'bg-slate-900 text-[#FFD000] border-2 border-[#FFD000] ring-4 ring-amber-500/20 shadow-[0_0_15px_rgba(255,208,0,0.4)]' : 'bg-slate-800 border border-slate-700 text-slate-500']">2</div>
            <div class="text-left hidden sm:block">
              <p class="text-xs font-bold uppercase tracking-wider" :class="currentStep === 2 ? 'text-[#FFD000]' : 'text-slate-500'">Tahap 2</p>
              <p class="text-[11px] text-slate-400 font-medium">Input Data Case</p>
            </div>
          </div>
        </div>
      </div>

      <!-- BANNER FORM LAMA -->
      <div v-if="!isNewForm" class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Menambahkan Case Untuk Form Existing:</p>
          <p class="text-sm font-extrabold text-slate-900 mt-0.5">{{ form?.name }} (Periode: {{ monthOptions.find(m => m.value === form?.month)?.label }} {{ form?.year }})</p>
        </div>
        <span class="bg-amber-200 text-amber-900 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase">Form Terkunci</span>
      </div>

      <!-- TAHAP 1 -->
      <div v-if="currentStep === 1" class="bg-white rounded-xl border border-slate-200 p-8 space-y-6 w-full shadow-xs">
        <div class="border-b border-slate-100 pb-4">
          <h2 class="text-base font-bold text-slate-900">Tahap 1: Pengaturan Form Bulanan</h2>
          <p class="text-xs text-slate-500 mt-1">Masukkan judul dan periode laporan bulanan yang akan dibuat.</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">
              NAMA FORM LAPORAN <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formHeader.name"
              type="text"
              required
              placeholder="Contoh: FORM 3A Juli 2026"
              class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-sm text-slate-900 font-semibold outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
            />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">BULAN <span class="text-red-500">*</span></label>
              <select v-model="formHeader.month" required class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-sm text-slate-900 font-semibold outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all">
                <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">TAHUN <span class="text-red-500">*</span></label>
              <select v-model="formHeader.year" required class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-sm text-slate-900 font-semibold outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all">
                <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
          <button type="button" class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-6 py-3 rounded-lg flex items-center gap-2 transition-all cursor-pointer shadow-sm" @click="goToStep2">
            <span>LANJUT KE INPUT CASE (TAHAP 2)</span>
            <ArrowRight class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- TAHAP 2 -->
      <form v-if="currentStep === 2" @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
          
          <!-- IDENTITAS PERKARA -->
          <div class="lg:col-span-5 bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-5 h-fit">
            <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
              <div class="p-2 bg-slate-100 text-slate-800 rounded-lg"><Gavel class="w-5 h-5" /></div>
              <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">IDENTITAS PERKARA & SATKER</h2>
            </div>
            
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">SATUAN KERJA <span class="text-red-500">*</span></label>
              <select v-model="formCase.satuanKerja" required class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all">
                <option value="" disabled>Pilih Satuan Kerja...</option>
                <option value="Kejaksaan Tinggi Aceh">Kejaksaan Tinggi Aceh</option>
                <option value="Kejaksaan Negeri Banda Aceh">Kejaksaan Negeri Banda Aceh</option>
              </select>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">KATEGORI TINDAK PIDANA <span class="text-red-500">*</span></label>
              <select v-model="formCase.kategoriTindakPidana" required class="w-full bg-[#FFFBEB] border border-amber-300 rounded-lg px-3.5 py-2.5 text-xs font-bold text-slate-900 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000] transition-all">
                <option value="" disabled>-- Pilih Jenis Tindak Pidana --</option>
                <option v-for="kat in kategoriPidanaOptions" :key="kat" :value="kat">{{ kat }}</option>
              </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">NO. REG BENDA SITAAN <span class="text-red-500">*</span></label>
                <input v-model="formCase.noRegBendaSitaan" type="text" required placeholder="B-000/O.1.10/..." class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">NO. REG PENYIDIKAN <span class="text-red-500">*</span></label>
                <input v-model="formCase.noRegPenyidikan" type="text" required placeholder="PRINT-00/O.1.10/..." class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">IDENTITAS TERSANGKA / TERDAKWA <span class="text-red-500">*</span></label>
              <textarea v-model="formCase.identitasTersangka" rows="3" required placeholder="Nama lengkap, alias, NIK, umur, pekerjaan..." class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"></textarea>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">PASAL YANG DISANGKAKAN / DIDAKWAKAN <span class="text-red-500">*</span></label>
              <input v-model="formCase.pasalDisangkakan" type="text" required placeholder="Contoh: Pasal 114 ayat (1) UU No. 35 Tahun 2009" class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
            </div>

            <div class="pt-3 border-t border-slate-100 space-y-4">
              <div class="flex items-center gap-2 text-slate-800 font-bold text-xs">
                <CheckSquare class="w-4 h-4 text-slate-600" />
                <span>STATUS & PUTUSAN</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">DISELESAIKAN</label>
                  <select v-model="formCase.statusDiselesaikan" class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3 py-2 text-xs text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]">
                    <option value="Belum Selesai">Belum Selesai (Dalam Proses)</option>
                    <option value="Dikembalikan">Dikembalikan kepada yang Berhak</option>
                    <option value="Dirampas Negara">Dirampas untuk Negara</option>
                    <option value="Dimusnahkan">Dimusnahkan</option>
                    <option value="Diserahkan">Diserahkan ke Instansi Lain</option>
                  </select>
                </div>
                <div>
                  <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">TGL PUTUSAN</label>
                  <input v-model="formCase.tglPelaksanaanPutusan" type="date" class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3 py-2 text-xs text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]" />
                </div>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">KETERANGAN</label>
                <textarea v-model="formCase.keterangan" rows="2" placeholder="Catatan proses..." class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000] resize-none"></textarea>
              </div>
            </div>
          </div>

          <!-- REPEATER MULTI BARANG BUKTI -->
          <div class="lg:col-span-7 space-y-5">
            <div class="flex items-center justify-between bg-white rounded-xl p-4 border border-slate-200/80 shadow-xs">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-slate-100 text-slate-800 rounded-lg"><Package class="w-5 h-5" /></div>
                <div>
                  <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">DAFTAR BARANG BUKTI</h2>
                  <p class="text-[11px] text-slate-500">Total: {{ formCase.barangBuktiList.length }} Barang Bukti</p>
                </div>
              </div>

              <button
                type="button"
                class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-3.5 py-2 rounded-lg flex items-center gap-1.5 transition-colors cursor-pointer shadow-xs"
                @click="addBarangBukti"
              >
                <Plus class="w-4 h-4" />
                <span>Tambah Barang Bukti</span>
              </button>
            </div>

            <div
              v-for="(bb, index) in formCase.barangBuktiList"
              :key="index"
              class="bg-white rounded-xl shadow-xs border border-slate-200/80 p-6 space-y-4 relative transition-all"
            >
              <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="bg-slate-100 text-slate-800 text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                  Barang Bukti #{{ index + 1 }}
                </span>

                <button
                  v-if="formCase.barangBuktiList.length > 1"
                  type="button"
                  class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded-lg transition-colors cursor-pointer"
                  @click="removeBarangBukti(index)"
                  title="Hapus Barang Bukti Ini"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">JENIS BARANG BUKTI <span class="text-red-500">*</span></label>
                  <input v-model="bb.jenisBarangBukti" type="text" required placeholder="e.g. Narkotika / Kendaraan" class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">NAMA BARANG BUKTI <span class="text-red-500">*</span></label>
                  <input v-model="bb.namaBarangBukti" type="text" required placeholder="e.g. Sabu Paket / Honda Vario" class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">JUMLAH <span class="text-red-500">*</span></label>
                  <!-- INPUT BISA ANGKA DESIMAL (step="any") -->
                  <input v-model="bb.jumlah" type="number" step="any" required placeholder="0.00" class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">SATUAN <span class="text-red-500">*</span></label>
                  <!-- SELECT DROPDOWN SATUAN -->
                  <select v-model="bb.satuan" required class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all">
                    <option value="" disabled>Pilih Satuan...</option>
                    <option v-for="s in satuanOptions" :key="s" :value="s">{{ s }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">TEMPAT PENYIMPANAN <span class="text-red-500">*</span></label>
                  <input v-model="bb.tempatPenyimpanan" type="text" required placeholder="Gudang BB Locker A2" class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-3.5 py-2.5 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                </div>
              </div>

              <div>
                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">UKURAN / DETAIL URAIAN BARANG <span class="text-red-500">*</span></label>
                <textarea v-model="bb.ukuranDetail" rows="2" required placeholder="Contoh: Plastik klip transparan berisi kristal putih..." class="w-full bg-[#F4F6F8] border border-transparent rounded-lg p-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all resize-none"></textarea>
              </div>
            </div>
          </div>

        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
          <div>
            <button v-if="isNewForm" type="button" class="bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer" @click="goToStep1">
              <ArrowLeft class="w-4 h-4" />
              <span>KEMBALI KE TAHAP 1</span>
            </button>
          </div>

          <div class="flex items-center gap-3">
            <button type="button" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer" @click="showPreviewModal = true">
              <Eye class="w-4 h-4" />
              <span>PREVIEW FORM</span>
            </button>
            <button type="button" class="bg-white hover:bg-slate-100 border border-slate-300 text-slate-800 text-xs font-bold px-5 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer" @click="resetCaseForm">
              <RotateCcw class="w-4 h-4" />
              <span>RESET CASE</span>
            </button>
            <button type="submit" class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-6 py-2.5 rounded-lg flex items-center gap-2 transition-colors shadow-sm cursor-pointer">
              <Save class="w-4 h-4" />
              <span>{{ isNewForm ? 'SIMPAN FORM & CASE' : 'SIMPAN CASE' }}</span>
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- PREVIEW MODAL (CASES TERSIMPAN + DRAFT BARU) -->
    <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-6xl rounded-xl bg-white border border-slate-200 shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between shrink-0 bg-slate-50">
          <div>
            <h2 class="text-sm font-bold text-slate-900">
              Preview Laporan Form - {{ isNewForm ? (formHeader.name || 'Form Baru') : props.form?.name }}
            </h2>
            <p class="text-[11px] text-slate-500 mt-0.5">
              Menampilkan total {{ (props.form?.cases?.length || 0) + 1 }} Perkara ({{ props.form?.cases?.length || 0 }} Tersimpan + 1 Draft Baru)
            </p>
          </div>
          <button type="button" class="text-slate-500 hover:text-slate-700 cursor-pointer" @click="showPreviewModal = false">
            <X class="w-4 h-4" />
          </button>
        </div>

        <div class="p-5 overflow-y-auto">
          <div class="overflow-x-auto rounded border border-slate-300">
            <table class="w-full text-left border-collapse">
              <thead class="bg-slate-800 text-white text-[10px] uppercase tracking-wider">
                <tr>
                  <th class="p-3 border border-slate-700">Satker & Kategori</th>
                  <th class="p-3 border border-slate-700">No. Register Sitaan & Sidik</th>
                  <th class="p-3 border border-slate-700">Tersangka & Pasal</th>
                  <th class="p-3 border border-slate-700">Daftar Barang Bukti</th>
                  <th class="p-3 border border-slate-700 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="text-xs text-slate-700">
                
                <!-- 1. CASES TERSIMPAN SEBELUMNYA -->
                <tr 
                  v-for="(c, cIdx) in (props.form?.cases || [])" 
                  :key="'prev-' + cIdx"
                  class="bg-slate-50/80 border-b border-slate-200"
                >
                  <td class="p-3 border border-slate-300 align-top">
                    <span class="inline-block bg-emerald-100 text-emerald-800 text-[10px] font-bold px-1.5 py-0.5 rounded mb-1">
                      Case #{{ cIdx + 1 }} (Tersimpan)
                    </span>
                    <div class="font-bold text-slate-900">{{ c.satuanKerja || '-' }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ c.kategoriTindakPidana || '-' }}</div>
                  </td>
                  <td class="p-3 border border-slate-300 align-top">
                    <div><span class="font-semibold text-slate-900">Sitaan:</span> {{ c.noRegBendaSitaan || '-' }}</div>
                    <div class="mt-1"><span class="font-semibold text-slate-900">Sidik:</span> {{ c.noRegPenyidikan || '-' }}</div>
                  </td>
                  <td class="p-3 border border-slate-300 align-top">
                    <div class="font-semibold text-slate-900">{{ c.identitasTersangka || '-' }}</div>
                    <div class="mt-1 text-slate-500">Pasal: {{ c.pasalDisangkakan || '-' }}</div>
                  </td>
                  <td class="p-3 border border-slate-300 align-top">
                    <div class="space-y-2">
                      <div
                        v-for="(bb, idx) in (c.barangBuktiList || [])"
                        :key="idx"
                        class="p-2 rounded bg-white border border-slate-200 text-xs"
                      >
                        <div class="font-bold text-slate-900">{{ idx + 1 }}. {{ bb.namaBarangBukti || 'Barang Bukti' }} ({{ bb.jenisBarangBukti || '-' }})</div>
                        <div class="text-[11px] text-slate-600 mt-0.5">{{ bb.ukuranDetail || '-' }}</div>
                        <div class="mt-1 flex items-center justify-between text-[11px]">
                          <span class="font-bold text-blue-700">Jumlah: {{ bb.jumlah || 0 }} {{ bb.satuan }}</span>
                          <span class="text-slate-500">Lokasi: {{ bb.tempatPenyimpanan || '-' }}</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="p-3 border border-slate-300 align-top text-center">
                    <span class="inline-block bg-slate-200 text-slate-800 px-2 py-1 rounded text-[10px] font-bold mb-2">
                      {{ c.statusDiselesaikan || '-' }}
                    </span>
                    <div class="text-[10px] text-slate-500 italic truncate max-w-[120px]" :title="c.keterangan">
                      {{ c.keterangan || 'Tidak ada ket.' }}
                    </div>
                  </td>
                </tr>

                <!-- 2. CASE DRAFT BARU YANG SEDANG DIISI -->
                <tr class="bg-amber-50/50 border-2 border-amber-300">
                  <td class="p-3 border border-slate-300 align-top">
                    <span class="inline-block bg-amber-200 text-amber-900 text-[10px] font-bold px-1.5 py-0.5 rounded mb-1">
                      Draft Baru
                    </span>
                    <div class="font-bold text-slate-900">{{ formCase.satuanKerja || '-' }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ formCase.kategoriTindakPidana || '-' }}</div>
                  </td>
                  <td class="p-3 border border-slate-300 align-top">
                    <div><span class="font-semibold text-slate-900">Sitaan:</span> {{ formCase.noRegBendaSitaan || '-' }}</div>
                    <div class="mt-1"><span class="font-semibold text-slate-900">Sidik:</span> {{ formCase.noRegPenyidikan || '-' }}</div>
                  </td>
                  <td class="p-3 border border-slate-300 align-top">
                    <div class="font-semibold text-slate-900">{{ formCase.identitasTersangka || '-' }}</div>
                    <div class="mt-1 text-slate-500">Pasal: {{ formCase.pasalDisangkakan || '-' }}</div>
                  </td>
                  <td class="p-3 border border-slate-300 align-top">
                    <div class="space-y-2">
                      <div
                        v-for="(bb, idx) in formCase.barangBuktiList"
                        :key="idx"
                        class="p-2 rounded bg-white border border-amber-200 text-xs"
                      >
                        <div class="font-bold text-slate-900">{{ idx + 1 }}. {{ bb.namaBarangBukti || 'Barang Bukti' }} ({{ bb.jenisBarangBukti || '-' }})</div>
                        <div class="text-[11px] text-slate-600 mt-0.5">{{ bb.ukuranDetail || '-' }}</div>
                        <div class="mt-1 flex items-center justify-between text-[11px]">
                          <span class="font-bold text-blue-700">Jumlah: {{ bb.jumlah || 0 }} {{ bb.satuan }}</span>
                          <span class="text-slate-500">Lokasi: {{ bb.tempatPenyimpanan || '-' }}</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="p-3 border border-slate-300 align-top text-center">
                    <span class="inline-block bg-amber-100 text-amber-800 px-2 py-1 rounded text-[10px] font-bold mb-2">
                      {{ formCase.statusDiselesaikan || '-' }}
                    </span>
                    <div class="text-[10px] text-slate-500 italic truncate max-w-[120px]" :title="formCase.keterangan">
                      {{ formCase.keterangan || 'Tidak ada ket.' }}
                    </div>
                  </td>
                </tr>

              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </AuthenticatedLayout>
</template>