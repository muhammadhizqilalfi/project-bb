<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import {
  FileText,
  Download,
  Calendar,
  Filter,
  FileSpreadsheet,
  Edit3,
  FileDown,
  CheckCircle2,
  Clock,
  Package
} from 'lucide-vue-next';

interface BarangBukti {
  jenisBarangBukti: string;
  namaBarangBukti?: string;
  jumlah: number | string;
  satuan: string;
  ukuranDetail?: string;
}

interface CaseItem {
  id: string;
  noReg: string;
  namaTersangka: string;
  kategoriBarang: string;
  beratGram: number;
  statusKontrol: 'Selesai (Siap Ekspor)' | 'Berjalan (Editable)';
  barangBuktiList: BarangBukti[];
}

interface Props {
  filters: {
    formType: '3A' | '3B' | '3C';
    month: number;
    year: number;
  };
  counts: {
    form3a: number;
    form3b: number;
    form3c: number;
  };
  summaryNarkotika: {
    sabuGram: number;
    ganjaGram: number;
    ekstasiPcs: number;
  };
  cases: CaseItem[];
}

const props = defineProps<Props>();

const activeMenu = ref('LAPORAN');

// State Filter
const selectedForm = ref<'3A' | '3B' | '3C'>(props.filters.formType || '3A');
const selectedMonth = ref<number>(props.filters.month || new Date().getMonth() + 1);
const selectedYear = ref<number>(props.filters.year || new Date().getFullYear());

// State Checkbox Selection
const selectedCaseIds = ref<string[]>([]);
const isAllSelected = computed(() => {
  return props.cases.length > 0 && selectedCaseIds.value.length === props.cases.length;
});

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

// Trigger Reload saat Filter Berubah
const applyFilters = () => {
  selectedCaseIds.value = [];
  router.get('/laporan', {
    formType: selectedForm.value,
    month: selectedMonth.value,
    year: selectedYear.value,
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const changeFormTab = (type: '3A' | '3B' | '3C') => {
  selectedForm.value = type;
  applyFilters();
};

// Toggle Checkbox
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedCaseIds.value = [];
  } else {
    selectedCaseIds.value = props.cases.map(c => c.id);
  }
};

const toggleSelectCase = (id: string) => {
  const index = selectedCaseIds.value.indexOf(id);
  if (index > -1) {
    selectedCaseIds.value.splice(index, 1);
  } else {
    selectedCaseIds.value.push(id);
  }
};

// Export Handlers
const exportSelectedPdf = () => {
  if (selectedCaseIds.value.length === 0) {
    alert('Pilih setidaknya satu laporan/perkara untuk diekspor!');
    return;
  }
  
  // Membuka endpoint PDF export pada window baru
  const ids = selectedCaseIds.value.join(',');
  window.open(`/laporan/export-pdf?formType=${selectedForm.value}&ids=${ids}`, '_blank');
};

const exportSinglePdf = (id: string) => {
  window.open(`/laporan/export-pdf?formType=${selectedForm.value}&ids=${id}`, '_blank');
};

const editCase = (id: string) => {
  router.get(`/form${selectedForm.value.toLowerCase()}/${id}/edit`);
};

// Helper Format Gram ke Kg
const formatKg = (gram: number) => {
  if (!gram) return '0.0';
  return (gram / 1000).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
};
</script>

<template>
  <Head title="Manajemen Laporan & Ekspor" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-6 bg-[#F4F6F9] min-h-screen">
      
      <!-- HEADER & FILTER PERIODE -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            Manajemen Laporan & Ekspor
          </h1>
          <p class="text-xs text-slate-500 mt-1">
            Rekapitulasi total barang bukti dan cetak berkas laporan bulanan Kejaksaan.
          </p>
        </div>

        <!-- FILTER BAR (BULAN & TAHUN) -->
        <div class="flex items-center gap-3 bg-white p-2 rounded-xl shadow-xs border border-slate-200">
          <div class="flex items-center gap-2 px-2 text-slate-500 border-r border-slate-200">
            <Calendar class="w-4 h-4 text-slate-600" />
            <span class="text-xs font-bold uppercase tracking-wider">Periode:</span>
          </div>

          <select 
            v-model="selectedMonth" 
            @change="applyFilters"
            class="bg-[#F4F6F8] border border-transparent rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-slate-300"
          >
            <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>

          <select 
            v-model="selectedYear" 
            @change="applyFilters"
            class="bg-[#F4F6F8] border border-transparent rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-slate-300"
          >
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>

      <!-- SUMMARY CARDS (KHUSUS NARKOTIKA PERIODIK) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- CARD 1: SABU -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
              <Package class="w-6 h-6" />
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL MASSA SABU</p>
              <div class="flex items-baseline gap-1.5 mt-0.5">
                <span class="text-2xl font-extrabold text-slate-900">{{ formatKg(summaryNarkotika.sabuGram) }}</span>
                <span class="text-xs font-semibold text-slate-500">Kilogram</span>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 2: GANJA -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
              <Package class="w-6 h-6" />
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL MASSA GANJA</p>
              <div class="flex items-baseline gap-1.5 mt-0.5">
                <span class="text-2xl font-extrabold text-slate-900">{{ formatKg(summaryNarkotika.ganjaGram) }}</span>
                <span class="text-xs font-semibold text-slate-500">Kilogram</span>
              </div>
            </div>
          </div>
        </div>

        <!-- CARD 3: EKSTASI / LAINNYA -->
        <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
              <Package class="w-6 h-6" />
            </div>
            <div>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">TOTAL EKSTASI / PIL</p>
              <div class="flex items-baseline gap-1.5 mt-0.5">
                <span class="text-2xl font-extrabold text-slate-900">{{ summaryNarkotika.ekstasiPcs.toLocaleString('id-ID') }}</span>
                <span class="text-xs font-semibold text-slate-500">Pcs / Butir</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FORM SELECTOR TABS -->
      <div class="flex items-center gap-2 bg-slate-200/60 p-1 rounded-xl w-fit">
        <button
          type="button"
          @click="changeFormTab('3A')"
          :class="[
            'px-5 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 cursor-pointer',
            selectedForm === '3A'
              ? 'bg-white text-slate-900 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
          ]"
        >
          <span>Laporan Form 3A</span>
          <span class="bg-slate-100 text-slate-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-slate-200">
            {{ String(counts.form3a).padStart(2, '0') }}
          </span>
        </button>

        <button
          type="button"
          @click="changeFormTab('3B')"
          :class="[
            'px-5 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 cursor-pointer',
            selectedForm === '3B'
              ? 'bg-white text-slate-900 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
          ]"
        >
          <span>Laporan Form 3B</span>
          <span class="bg-slate-100 text-slate-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-slate-200">
            {{ String(counts.form3b).padStart(2, '0') }}
          </span>
        </button>

        <button
          type="button"
          @click="changeFormTab('3C')"
          :class="[
            'px-5 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 cursor-pointer',
            selectedForm === '3C'
              ? 'bg-white text-slate-900 shadow-xs'
              : 'text-slate-600 hover:text-slate-900'
          ]"
        >
          <span>Laporan Form 3C</span>
          <span class="bg-slate-100 text-slate-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-slate-200">
            {{ String(counts.form3c).padStart(2, '0') }}
          </span>
        </button>
      </div>

      <!-- MAIN DATA PREVIEW TABLE -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        
        <!-- TOOLBAR ATAS TABEL -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-4 bg-slate-50/50">
          <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
              <input 
                type="checkbox" 
                :checked="isAllSelected" 
                @change="toggleSelectAll"
                class="w-4 h-4 rounded border-slate-300 text-[#FFD000] focus:ring-[#FFD000]" 
              />
              <span>Pilih Semua Terpilih ({{ selectedCaseIds.length }})</span>
            </label>
          </div>

          <div class="flex items-center gap-3">
            <!-- TOMBOL EKSPOR PDF TERPILIH (Warna Kuning Khas #FFD000) -->
            <button
              type="button"
              @click="exportSelectedPdf"
              class="bg-[#FFD000] hover:bg-yellow-400 text-slate-950 text-xs font-extrabold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-all cursor-pointer shadow-xs"
            >
              <FileDown class="w-4 h-4 stroke-[2.5]" />
              <span>Ekspor PDF Terpilih</span>
            </button>
          </div>
        </div>

        <!-- TABEL PREVIEW CASE -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">
                <th class="p-4 w-12 text-center"></th>
                <th class="p-4">ID PERKARA / NO REG</th>
                <th class="p-4">NAMA TERSANGKA</th>
                <th class="p-4">KATEGORI BARANG</th>
                <th class="p-4 text-right">BERAT / JUMLAH</th>
                <th class="p-4 text-center">STATUS KONTROL</th>
                <th class="p-4 text-center w-28">TINDAKAN</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
              
              <tr 
                v-for="item in cases" 
                :key="item.id" 
                class="hover:bg-slate-50/80 transition-colors"
              >
                <!-- CHECKBOX -->
                <td class="p-4 text-center">
                  <input 
                    type="checkbox" 
                    :value="item.id"
                    :checked="selectedCaseIds.includes(item.id)"
                    @change="toggleSelectCase(item.id)"
                    class="w-4 h-4 rounded border-slate-300 text-[#FFD000] focus:ring-[#FFD000] cursor-pointer"
                  />
                </td>

                <!-- ID PERKARA / NO REG -->
                <td class="p-4">
                  <div class="font-extrabold text-slate-900">{{ item.noReg }}</div>
                  <div class="text-[10px] text-slate-400 font-semibold mt-0.5">ID: {{ item.id }}</div>
                </td>

                <!-- NAMA TERSANGKA -->
                <td class="p-4 font-bold text-slate-800">
                  {{ item.namaTersangka || '-' }}
                </td>

                <!-- KATEGORI BARANG -->
                <td class="p-4">
                  <span class="inline-block bg-blue-50 text-blue-700 font-bold px-2.5 py-1 rounded-md text-[10px] uppercase border border-blue-100">
                    {{ item.kategoriBarang }}
                  </span>
                </td>

                <!-- BERAT / JUMLAH -->
                <td class="p-4 text-right font-extrabold text-slate-900">
                  {{ item.beratGram.toLocaleString('id-ID') }} <span class="text-[10px] text-slate-400 font-normal">GR</span>
                </td>

                <!-- STATUS KONTROL -->
                <td class="p-4 text-center">
                  <span 
                    v-if="item.statusKontrol === 'Selesai (Siap Ekspor)'"
                    class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-800 font-bold px-3 py-1 rounded-full text-[10px]"
                  >
                    <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                    Selesai (Siap Ekspor)
                  </span>
                  <span 
                    v-else
                    class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 font-bold px-3 py-1 rounded-full text-[10px]"
                  >
                    <Clock class="w-3 h-3 text-slate-400" />
                    Berjalan (Editable)
                  </span>
                </td>

                <!-- ACTION BUTTONS -->
                <td class="p-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <!-- EDIT -->
                    <button 
                      type="button" 
                      @click="editCase(item.id)"
                      class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                      title="Edit Case"
                    >
                      <Edit3 class="w-4 h-4" />
                    </button>

                    <!-- DOWNLOAD INDIVIDUAL PDF -->
                    <button 
                      type="button" 
                      @click="exportSinglePdf(item.id)"
                      class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                      title="Cetak PDF Perkara Ini"
                    >
                      <FileDown class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <!-- EMPTY STATE -->
              <tr v-if="cases.length === 0">
                <td colspan="7" class="p-12 text-center text-slate-400">
                  <FileText class="w-10 h-10 mx-auto stroke-1 text-slate-300 mb-2" />
                  <p class="text-xs font-bold text-slate-600">Tidak ada laporan data perkara ditemukan pada periode ini.</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">Silakan ubah filter bulan, tahun, atau pilihan form di atas.</p>
                </td>
              </tr>

            </tbody>
          </table>
        </div>

        <!-- FOOTER PAGINATION PREVIEW -->
        <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 bg-slate-50/50">
          <span>Menampilkan {{ cases.length }} Laporan Form {{ selectedForm }}</span>
          <div class="font-semibold text-slate-700">
            Periode: {{ monthOptions.find(m => m.value === selectedMonth)?.label }} {{ selectedYear }}
          </div>
        </div>

      </div>

    </div>
  </AuthenticatedLayout>
</template>