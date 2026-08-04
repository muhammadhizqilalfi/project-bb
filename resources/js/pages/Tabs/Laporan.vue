<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import {
  FileText,
  Calendar,
  Filter,
  Edit3,
  FileDown,
  Package
} from 'lucide-vue-next';

interface BarangBukti3A {
  jenisBarangBukti?: string;
  namaBarangBukti?: string;
  jumlah?: number | string;
  satuan?: string;
  ukuranDetail?: string;
  tempatPenyimpanan?: string;
}

interface BarangBukti3C {
  jenisBarangBukti?: string;
  macamJenisKadar?: string;
  jumlahSatuan?: number | string;
  jenisSatuan?: string;
  tempatPenyimpanan?: string;
}

interface CaseItem {
  id: string;
  // Common / 3A Fields
  satuanKerja?: string;
  kejaksaan?: string;
  noRegSitaan?: string;
  noRegSidik?: string;
  identitasTersangka?: string;
  pasalDisangkakan?: string;
  pasalDidakwakan?: string;
  statusDiselesaikan?: string;
  tglPelaksanaanPutusan?: string;
  keterangan?: string;
  barangBuktiList?: BarangBukti3A[] | BarangBukti3C[];
  
  // 3B Fields
  sisaBulanLalu?: number | string;
  masukBulanLaporan?: number | string;
  jumlahBulanLaporan?: number | string;
  sisaBulanLaporan?: number | string;

  // 3C Fields
  tglPenerimaan?: string;
  noKepPengadilan?: string;
  tglKepPengadilan?: string;
  amarPutusan?: string;
}

interface Props {
  filters: {
    formType: '3A' | '3B' | '3C';
    month: number;
    year: number;
    kategori?: string;
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
const selectedKategori = ref<string>(props.filters.kategori || 'ALL');

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
  { value: 'ALL', label: 'Semua Tindak Pidana' },
  { value: 'KAMNEGTIBUM DAN TPUL', label: 'KAMNEGTIBUM DAN TPUL' },
  { value: 'NARKOTIKA DAN ZAT ADITIF LAINNYA', label: 'NARKOTIKA DAN ZAT ADITIF' },
  { value: 'OHARDA', label: 'OHARDA' },
  { value: 'TERORIS', label: 'TERORIS' },
  { value: 'KORUPSI', label: 'KORUPSI' }
];

// Trigger Reload saat Filter Berubah
const applyFilters = () => {
  router.get('/laporan', {
    formType: selectedForm.value,
    month: selectedMonth.value,
    year: selectedYear.value,
    kategori: selectedKategori.value,
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

const changeFormTab = (type: '3A' | '3B' | '3C') => {
  selectedForm.value = type;
  applyFilters();
};

// Export Handler Berdasarkan Form & Periode
const exportFormPdf = () => {
  window.open(
    `/laporan/export-pdf?formType=${selectedForm.value}&month=${selectedMonth.value}&year=${selectedYear.value}&kategori=${selectedKategori.value}`,
    '_blank'
  );
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
      
      <!-- HEADER & FILTER BAR -->
      <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">
            Manajemen Laporan & Ekspor
          </h1>
          <p class="text-xs text-slate-500 mt-1">
            Rekapitulasi total barang bukti dan cetak berkas laporan bulanan Kejaksaan.
          </p>
        </div>

        <!-- FILTER BAR (TINDAK PIDANA & PERIODE BULAN/TAHUN) -->
        <div class="flex flex-wrap items-center gap-3 bg-white p-2 rounded-xl shadow-xs border border-slate-200">
          <div class="flex items-center gap-2 px-2 text-slate-500 border-r border-slate-200">
            <Filter class="w-4 h-4 text-slate-600" />
            <span class="text-xs font-bold uppercase tracking-wider">Tindak Pidana:</span>
          </div>
          <select 
            v-model="selectedKategori" 
            @change="applyFilters"
            class="bg-[#F4F6F8] border border-transparent rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-slate-300"
          >
            <option v-for="k in kategoriPidanaOptions" :key="k.value" :value="k.value">{{ k.label }}</option>
          </select>

          <div class="flex items-center gap-2 px-2 text-slate-500 border-l border-r border-slate-200">
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

      <!-- TABS FORM & BARIS TOMBOL EKSPOR -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 bg-slate-200/60 p-1 rounded-xl w-fit">
          <button
            type="button"
            @click="changeFormTab('3A')"
            :class="[
              'px-5 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 cursor-pointer',
              selectedForm === '3A' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
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
              selectedForm === '3B' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
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
              selectedForm === '3C' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <span>Laporan Form 3C</span>
            <span class="bg-slate-100 text-slate-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-slate-200">
              {{ String(counts.form3c).padStart(2, '0') }}
            </span>
          </button>
        </div>

        <button
          type="button"
          @click="exportFormPdf"
          class="bg-[#FFD000] hover:bg-yellow-400 text-slate-950 text-xs font-extrabold px-5 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer shadow-xs border border-amber-300 w-fit"
        >
          <FileDown class="w-4 h-4 stroke-[2.5]" />
          <span>Ekspor Laporan Form {{ selectedForm }}</span>
        </button>
      </div>

      <!-- MAIN DATA PREVIEW TABLE DENGAN KETENTUAN MASING-MASING TEMPLATE FORM -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-300 overflow-hidden">
        <div class="overflow-x-auto">
          
          <!-- ========================================== -->
          <!-- 1. TEMPLATE PREVIEW FORM 3A                -->
          <!-- ========================================== -->
          <table v-if="selectedForm === '3A'" class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 text-slate-900 border-b border-slate-300 font-bold text-[11px] uppercase tracking-wider text-center">
              <tr class="divide-x divide-slate-300 border-b border-slate-300">
                <th class="p-3 w-12">No. Urut</th>
                <th class="p-3">Satuan Kerja</th>
                <th class="p-3">Register Benda Sitaan Barang Bukti</th>
                <th class="p-3">Register Tahap Penyidikan</th>
                <th class="p-3">Uraian Benda Sitaan Jumlah / Satuan / Jenis Barang / Ukuran</th>
                <th class="p-3">Tempat Penyimpanan</th>
                <th class="p-3">Identitas Tersangka / Terdakwa</th>
                <th class="p-3">Pasal yang disangkakan / didakwakan</th>
                <th class="p-3">Diselesaikan</th>
                <th class="p-3">Tanggal Pelaksanaan Putusan Hakim & Ijin Jaksa Agung</th>
                <th class="p-3">Keterangan</th>
                <th class="p-3 w-16">Aksi</th>
              </tr>
              <!-- BARIS NOMOR KOLOM RESMI (1 - 11) -->
              <tr class="bg-slate-100 divide-x divide-slate-300 text-[10px] text-slate-500 font-semibold border-b border-slate-300">
                <td class="p-1">1</td>
                <td class="p-1">2</td>
                <td class="p-1">3</td>
                <td class="p-1">4</td>
                <td class="p-1">5</td>
                <td class="p-1">6</td>
                <td class="p-1">7</td>
                <td class="p-1">8</td>
                <td class="p-1">9</td>
                <td class="p-1">10</td>
                <td class="p-1">11</td>
                <td class="p-1">-</td>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-800">
              <tr v-for="(item, idx) in cases" :key="item.id" class="hover:bg-slate-50 transition-colors divide-x divide-slate-200">
                <td class="p-3 text-center font-bold">{{ idx + 1 }}</td>
                <td class="p-3 font-semibold">{{ item.satuanKerja || item.kejaksaan || '-' }}</td>
                <td class="p-3 font-bold text-slate-900">{{ item.noRegSitaan || '-' }}</td>
                <td class="p-3">{{ item.noRegSidik || '-' }}</td>
                
                <!-- URAIAN BENDA SITAAN (LIST BARANG BUKTI) -->
                <td class="p-3">
                  <div class="space-y-2">
                    <div v-for="(bb, bIdx) in (item.barangBuktiList || [])" :key="bIdx" class="bg-slate-50 p-2 rounded border border-slate-200 text-[11px]">
                      <div class="font-bold text-slate-900">{{ bIdx + 1 }}. {{ (bb as BarangBukti3A).namaBarangBukti || (bb as BarangBukti3A).jenisBarangBukti || 'BB' }}</div>
                      <div>Detail: {{ (bb as BarangBukti3A).ukuranDetail || '-' }}</div>
                      <div class="font-bold text-blue-700 mt-0.5">Jumlah: {{ (bb as BarangBukti3A).jumlah || 0 }} {{ (bb as BarangBukti3A).satuan }}</div>
                    </div>
                  </div>
                </td>

                <td class="p-3">{{ (item.barangBuktiList?.[0] as BarangBukti3A)?.tempatPenyimpanan || '-' }}</td>
                <td class="p-3 font-semibold">{{ item.identitasTersangka || '-' }}</td>
                <td class="p-3">{{ item.pasalDisangkakan || item.pasalDidakwakan || '-' }}</td>
                <td class="p-3 text-center">
                  <span class="inline-block bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded text-[10px]">
                    {{ item.statusDiselesaikan || '-' }}
                  </span>
                </td>
                <td class="p-3 text-center">{{ item.tglPelaksanaanPutusan || '-' }}</td>
                <td class="p-3 text-slate-600 italic">{{ item.keterangan || '-' }}</td>
                <td class="p-3 text-center">
                  <button type="button" @click="editCase(item.id)" class="p-1 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded">
                    <Edit3 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>


          <!-- ========================================== -->
          <!-- 2. TEMPLATE PREVIEW FORM 3B                -->
          <!-- ========================================== -->
          <table v-if="selectedForm === '3B'" class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 text-slate-900 border-b border-slate-300 font-bold text-[11px] uppercase tracking-wider text-center">
              <tr class="divide-x divide-slate-300 border-b border-slate-300">
                <th class="p-3 w-12">No. Urut</th>
                <th class="p-3">Kejaksaan</th>
                <th class="p-3">Sisa Bulan Lalu</th>
                <th class="p-3">Masuk Bulan Laporan</th>
                <th class="p-3">Jumlah Bulan Laporan</th>
                <th class="p-3">Sisa Bulan Laporan</th>
                <th class="p-3">Keterangan</th>
                <th class="p-3 w-16">Aksi</th>
              </tr>
              <!-- BARIS NOMOR KOLOM RESMI (1 - 7) -->
              <tr class="bg-slate-100 divide-x divide-slate-300 text-[10px] text-slate-500 font-semibold border-b border-slate-300">
                <td class="p-1">1</td>
                <td class="p-1">2</td>
                <td class="p-1">3</td>
                <td class="p-1">4</td>
                <td class="p-1">5</td>
                <td class="p-1">6</td>
                <td class="p-1">7</td>
                <td class="p-1">-</td>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-800">
              <tr v-for="(item, idx) in cases" :key="item.id" class="hover:bg-slate-50 transition-colors divide-x divide-slate-200 text-center">
                <td class="p-3 font-bold">{{ idx + 1 }}</td>
                <td class="p-3 font-semibold text-left">{{ item.kejaksaan || item.satuanKerja || '-' }}</td>
                <td class="p-3 font-medium">{{ item.sisaBulanLalu || '0' }}</td>
                <td class="p-3 font-medium">{{ item.masukBulanLaporan || '0' }}</td>
                <td class="p-3 font-bold text-blue-700">{{ item.jumlahBulanLaporan || '0' }}</td>
                <td class="p-3 font-medium">{{ item.sisaBulanLaporan || '0' }}</td>
                <td class="p-3 text-slate-600 italic text-left">{{ item.keterangan || '-' }}</td>
                <td class="p-3">
                  <button type="button" @click="editCase(item.id)" class="p-1 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded">
                    <Edit3 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>


          <!-- ========================================== -->
          <!-- 3. TEMPLATE PREVIEW FORM 3C                -->
          <!-- ========================================== -->
          <table v-if="selectedForm === '3C'" class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 text-slate-900 border-b border-slate-300 font-bold text-[11px] uppercase tracking-wider text-center">
              <tr class="divide-x divide-slate-300 border-b border-slate-300">
                <th class="p-3 w-12">No. Urut</th>
                <th class="p-3">Kejaksaan</th>
                <th class="p-3">Jenis Barang Bukti</th>
                <th class="p-3">Pasal yang didakwakan</th>
                <th class="p-3">Register Benda Sitaan / Tanggal Penerimaan</th>
                <th class="p-3">Macam Jenis Kadar</th>
                <th class="p-3">Jumlah Satuan</th>
                <th class="p-3">Jenis Satuan</th>
                <th class="p-3">Tempat Penyimpanan</th>
                <th class="p-3">Tgl & No. KEP PN/PT/MA</th>
                <th class="p-3">Amar Putusan</th>
                <th class="p-3">Tanggal Pelaksanaan Putusan Hakim</th>
                <th class="p-3 w-16">Aksi</th>
              </tr>
              <!-- BARIS NOMOR KOLOM RESMI (1 - 12) -->
              <tr class="bg-slate-100 divide-x divide-slate-300 text-[10px] text-slate-500 font-semibold border-b border-slate-300">
                <td class="p-1">1</td>
                <td class="p-1">2</td>
                <td class="p-1">3</td>
                <td class="p-1">4</td>
                <td class="p-1">5</td>
                <td class="p-1">6</td>
                <td class="p-1">7</td>
                <td class="p-1">8</td>
                <td class="p-1">9</td>
                <td class="p-1">10</td>
                <td class="p-1">11</td>
                <td class="p-1">12</td>
                <td class="p-1">-</td>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-800">
              <tr v-for="(item, idx) in cases" :key="item.id" class="hover:bg-slate-50 transition-colors divide-x divide-slate-200">
                <td class="p-3 text-center font-bold">{{ idx + 1 }}</td>
                <td class="p-3 font-semibold">{{ item.kejaksaan || item.satuanKerja || '-' }}</td>
                
                <!-- JENIS BARANG BUKTI -->
                <td class="p-3">
                  <div class="space-y-1">
                    <div v-for="(bb, bIdx) in (item.barangBuktiList || [])" :key="bIdx" class="font-bold text-slate-900">
                      {{ bIdx + 1 }}. {{ (bb as BarangBukti3C).jenisBarangBukti || '-' }}
                    </div>
                  </div>
                </td>

                <td class="p-3 font-medium">{{ item.pasalDidakwakan || item.pasalDisangkakan || '-' }}</td>
                
                <td class="p-3">
                  <div class="font-bold text-slate-900">{{ item.noRegSitaan || '-' }}</div>
                  <div class="text-[10px] text-slate-500">Tgl: {{ item.tglPenerimaan || '-' }}</div>
                </td>

                <!-- MACAM JENIS KADAR -->
                <td class="p-3">
                  <div v-for="(bb, bIdx) in (item.barangBuktiList || [])" :key="bIdx" class="text-[11px] text-slate-700">
                    {{ (bb as BarangBukti3C).macamJenisKadar || '-' }}
                  </div>
                </td>

                <!-- JUMLAH SATUAN -->
                <td class="p-3 text-right font-extrabold text-blue-700">
                  <div v-for="(bb, bIdx) in (item.barangBuktiList || [])" :key="bIdx">
                    {{ (bb as BarangBukti3C).jumlahSatuan || 0 }}
                  </div>
                </td>

                <!-- JENIS SATUAN -->
                <td class="p-3 text-center font-medium">
                  <div v-for="(bb, bIdx) in (item.barangBuktiList || [])" :key="bIdx">
                    {{ (bb as BarangBukti3C).jenisSatuan || '-' }}
                  </div>
                </td>

                <!-- TEMPAT PENYIMPANAN -->
                <td class="p-3">
                  <div v-for="(bb, bIdx) in (item.barangBuktiList || [])" :key="bIdx" class="text-[11px]">
                    {{ (bb as BarangBukti3C).tempatPenyimpanan || '-' }}
                  </div>
                </td>

                <td class="p-3">
                  <div class="font-semibold text-slate-900">{{ item.noKepPengadilan || '-' }}</div>
                  <div class="text-[10px] text-slate-500">Tgl: {{ item.tglKepPengadilan || '-' }}</div>
                </td>

                <td class="p-3 font-medium text-slate-900">{{ item.amarPutusan || '-' }}</td>
                <td class="p-3 text-center">{{ item.tglPelaksanaanPutusan || '-' }}</td>
                
                <td class="p-3 text-center">
                  <button type="button" @click="editCase(item.id)" class="p-1 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded">
                    <Edit3 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>


          <!-- ========================================== -->
          <!-- EMPTY STATE (JIKA TIDAK ADA DATA)          -->
          <!-- ========================================== -->
          <div v-if="cases.length === 0" class="p-12 text-center text-slate-400">
            <FileText class="w-10 h-10 mx-auto stroke-1 text-slate-300 mb-2" />
            <p class="text-xs font-bold text-slate-600">Tidak ada laporan data perkara ditemukan pada periode/kategori ini.</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Silakan ubah filter tindak pidana, bulan, tahun, atau pilihan form di atas.</p>
          </div>

        </div>

        <!-- FOOTER PREVIEW -->
        <div class="p-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 bg-slate-50">
          <span>Menampilkan {{ cases.length }} Laporan Form {{ selectedForm }}</span>
          <div class="font-semibold text-slate-700">
            Periode: {{ monthOptions.find(m => m.value === selectedMonth)?.label }} {{ selectedYear }}
          </div>
        </div>

      </div>

    </div>
  </AuthenticatedLayout>
</template>