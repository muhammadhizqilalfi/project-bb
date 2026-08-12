<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import { FileText, Calendar, Filter, Edit3, Trash2, FileDown, Package, ChevronDown, FileCode } from 'lucide-vue-next';

interface BarangBuktiItem {
  jenisBarangBukti?: string;
  namaBarangBukti?: string;
  uraianBarangBukti?: string;
  jumlah?: number | string;
  jumlahNarkotika?: number | string;
  jumlahSatuan?: number | string;
  satuan?: string;
  satuanNarkotika?: string;
  jenisSatuan?: string;
  ukuranDetail?: string;
  tempatPenyimpanan?: string;
  macamJenisKadar?: string;
  amarPutusan?: string;
  uraianPutusan?: string;
}

interface CaseItem {
  id: string;
  case_index?: number;
  satuanKerja?: string;
  noRegBendaSitaan?: string;
  noRegPenyidikan?: string;
  identitasTersangka?: string;
  pasalDisangkakan?: string;
  pasalDidakwakan?: string;
  statusDiselesaikan?: string;
  tglPelaksanaanPutusan?: string;
  keterangan?: string;
  barangBuktiList?: BarangBuktiItem[];
  tglRegPenyidikan?: string;
  tglPenerimaan?: string;

  // 3B Fields
  sisaBulanLalu?: number | string;
  masukBulanLaporan?: number | string;
  jumlahBulanLaporan?: number | string;
  sisaBulanLaporan?: number | string;

  // 3C Fields
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

// Helper Mengubah Angka ke Teks Terbilang
const angkaKeTeks = (num: number): string => {
  if (num === 0) return 'Nol';
  const satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
  if (num < 12) return satuan[num];
  if (num < 20) return angkaKeTeks(num - 10) + ' Belas';
  if (num < 100) return angkaKeTeks(Math.floor(num / 10)) + ' Puluh' + (num % 10 !== 0 ? ' ' + angkaKeTeks(num % 10) : '');
  if (num < 200) return 'Seratus' + (num % 100 !== 0 ? ' ' + angkaKeTeks(num % 100) : '');
  if (num < 1000) return angkaKeTeks(Math.floor(num / 100)) + ' Ratus' + (num % 100 !== 0 ? ' ' + angkaKeTeks(num % 100) : '');
  if (num < 2000) return 'Seribu' + (num % 1000 !== 0 ? ' ' + angkaKeTeks(num % 1000) : '');
  if (num < 1000000) return angkaKeTeks(Math.floor(num / 1000)) + ' Ribu' + (num % 1000 !== 0 ? ' ' + angkaKeTeks(num % 1000) : '');
  if (num < 1000000000) return angkaKeTeks(Math.floor(num / 1000000)) + ' Juta' + (num % 1000000 !== 0 ? ' ' + angkaKeTeks(num % 1000000) : '');
  return String(num);
};

// Format Jumlah + Terbilang
const formatJumlahTerbilang = (val: any): string => {
  if (val === undefined || val === null || val === '') return '-';
  const num = typeof val === 'number' ? val : parseFloat(String(val).replace(/,/g, '.'));
  if (isNaN(num)) return String(val);
  if (Number.isInteger(num)) {
    return `${num} (${angkaKeTeks(num)})`;
  }
  return `${num}`;
};

// Helper Type-Safe untuk memastikan array bertipe BarangBuktiItem[]
const getBbList = (list?: BarangBuktiItem[]): BarangBuktiItem[] => {
  if (!list || list.length === 0) {
    return [{} as BarangBuktiItem];
  }
  return list;
};

// Helper Type-Safe logika 'Sda' (Sama dengan atas) untuk Amar Putusan
const isSdaAmarPutusan = (list: BarangBuktiItem[] | undefined, index: number): boolean => {
  if (!list || index <= 0) return false;
  const current = list[index];
  const prev = list[index - 1];
  if (!current || !prev) return false;
  return Boolean(
    current.amarPutusan &&
    current.amarPutusan === prev.amarPutusan &&
    current.uraianPutusan === prev.uraianPutusan
  );
};

// Logika Rowspan Tempat Penyimpanan (Gudang yang sama digabung, gudang beda dipisah baris/garis)
const shouldRenderTempatPenyimpanan = (list: BarangBuktiItem[] | undefined, index: number): boolean => {
  if (!list || list.length === 0) return true;
  if (index === 0) return true;
  return list[index]?.tempatPenyimpanan !== list[index - 1]?.tempatPenyimpanan;
};

const getTempatPenyimpananRowspan = (list: BarangBuktiItem[] | undefined, index: number): number => {
  if (!list || list.length === 0) return 1;
  let count = 1;
  for (let i = index + 1; i < list.length; i++) {
    if (list[i]?.tempatPenyimpanan === list[index]?.tempatPenyimpanan) {
      count++;
    } else {
      break;
    }
  }
  return count;
};

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

const editCase = (id: string, index?: number) => {
  const query = index !== undefined ? `?index=${index}` : '';
  if (selectedForm.value === '3A') {
    router.get(`/form3a/${id}/edit${query}`);
  } else {
    router.get(`/form${selectedForm.value.toLowerCase()}/${id}/edit${query}`);
  }
};

const deleteCase = (id: string, index?: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus data perkara ini?')) {
    const query = index !== undefined ? `?index=${index}` : '';
    router.delete(`/form${selectedForm.value.toLowerCase()}/${id}${query}`, {
      preserveScroll: true,
    });
  }
};

const formatKg = (gram: number) => {
  if (!gram) return '0.0';
  return (gram / 1000).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
};

const isExportOpen = ref(false)

const exportFormPdf = () => {
  if (selectedKategori.value === 'ALL') {
    alert('Silahkan pilih salah satu kategori Tindak Pidana terlebih dahulu sebelum mengekspor laporan.');
    return;
  }

  window.open(
    `/laporan/export-pdf?formType=${selectedForm.value}&month=${selectedMonth.value}&year=${selectedYear.value}&kategori=${selectedKategori.value}`,
    '_blank'
  );
};

const exportFormDocx = () => {
  if (selectedKategori.value === 'ÁLL') {
    alert('Silahkan pilih salah satu kategori Tindak Pidana terlebih dahulu sebelum mengekspor laporan.');
    return;
  }

  window.open(
    `/laporan/export-docx?formType=${selectedForm.value}&month=${selectedMonth.value}&year=${selectedYear.value}&kategori=${selectedKategori.value}`,
    '_blank'
  );
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

        <div class="flex flex-wrap items-center gap-3 bg-white p-2 rounded-xl shadow-xs border border-slate-200">
          <div class="flex items-center gap-2 px-2 text-slate-500 border-r border-slate-200">
            <Filter class="w-4 h-4 text-slate-600" />
            <span class="text-xs font-bold uppercase tracking-wider">Tindak Pidana:</span>
          </div>
          <select v-model="selectedKategori" @change="applyFilters"
            class="bg-[#F4F6F8] border border-transparent rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-slate-300">
            <option v-for="k in kategoriPidanaOptions" :key="k.value" :value="k.value">{{ k.label }}</option>
          </select>

          <div class="flex items-center gap-2 px-2 text-slate-500 border-l border-r border-slate-200">
            <Calendar class="w-4 h-4 text-slate-600" />
            <span class="text-xs font-bold uppercase tracking-wider">Periode:</span>
          </div>

          <select v-model="selectedMonth" @change="applyFilters"
            class="bg-[#F4F6F8] border border-transparent rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-slate-300">
            <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>

          <select v-model="selectedYear" @change="applyFilters"
            class="bg-[#F4F6F8] border border-transparent rounded-lg px-3 py-1.5 text-xs font-bold text-slate-800 outline-none focus:bg-white focus:border-slate-300">
            <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
          </select>
        </div>
      </div>

      <!-- SUMMARY CARDS -->
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
                <span class="text-2xl font-extrabold text-slate-900">{{
                  summaryNarkotika.ekstasiPcs.toLocaleString('id-ID') }}</span>
                <span class="text-xs font-semibold text-slate-500">Pcs / Butir</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TABS FORM & BARIS TOMBOL EKSPOR -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 bg-slate-200/60 p-1 rounded-xl w-fit">
          <button type="button" @click="changeFormTab('3A')" :class="[
            'px-5 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 cursor-pointer',
            selectedForm === '3A' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
          ]">
            <span>Laporan Form 3A</span>
            <span
              class="bg-slate-100 text-slate-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-slate-200">
              {{ String(counts.form3a).padStart(2, '0') }}
            </span>
          </button>

          <button type="button" @click="changeFormTab('3B')" :class="[
            'px-5 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 cursor-pointer',
            selectedForm === '3B' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
          ]">
            <span>Laporan Form 3B</span>
            <span
              class="bg-slate-100 text-slate-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-slate-200">
              {{ String(counts.form3b).padStart(2, '0') }}
            </span>
          </button>

          <button type="button" @click="changeFormTab('3C')" :class="[
            'px-5 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2 cursor-pointer',
            selectedForm === '3C' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'
          ]">
            <span>Laporan Form 3C</span>
            <span
              class="bg-slate-100 text-slate-800 text-[10px] font-extrabold px-2 py-0.5 rounded-full border border-slate-200">
              {{ String(counts.form3c).padStart(2, '0') }}
            </span>
          </button>
        </div>

        <div class="relative w-fit">
          <!-- Tombol Utama (Trigger Dropdown) -->
          <button type="button" @click="isExportOpen = !isExportOpen" :disabled="selectedKategori === 'ALL'" :class="[
            'text-xs font-extrabold px-5 py-2.5 rounded-xl flex items-center gap-2 transition-all cursor-pointer shadow-xs border w-fit',
            selectedKategori === 'ALL'
              ? 'bg-slate-200 text-slate-400 border-slate-300 cursor-not-allowed'
              : 'bg-[#FFD000] hover:bg-yellow-400 text-slate-950 border-amber-300'
          ]" :title="selectedKategori === 'ALL' ? 'Pilih kategori Tindak Pidana terlebih dahulu untuk mengekspor' : ''">
            <FileDown class="w-4 h-4 stroke-[2.5]" />
            <span>Ekspor Laporan Form {{ selectedForm }}</span>

            <!-- Ikon Panah Kecil (Indikator Dropdown) -->
            <ChevronDown class="w-3.5 h-3.5 stroke-[2.5] transition-transform duration-200"
              :class="{ 'rotate-180': isExportOpen }" />
          </button>

          <!-- Menu Pilihan PDF & DOCX -->
          <div v-if="isExportOpen && selectedKategori !== 'ALL'" @click.outside="isExportOpen = false"
            class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1.5 z-50">
            <!-- Pilihan PDF -->
            <button type="button" @click="exportFormPdf(); isExportOpen = false"
              class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-slate-900 flex items-center gap-2.5 transition-colors">
              <FileText class="w-4 h-4 text-red-500" />
              <span>Ekspor ke PDF</span>
            </button>

            <!-- Pilihan DOCX -->
            <button type="button" @click="exportFormDocx(); isExportOpen = false"
              class="w-full text-left px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 hover:text-slate-900 flex items-center gap-2.5 transition-colors border-t border-slate-100">
              <FileCode class="w-4 h-4 text-blue-600" />
              <span>Ekspor ke DOCX</span>
            </button>
          </div>
        </div>
      </div>

      <!-- MAIN DATA PREVIEW TABLE -->
      <div class="bg-white rounded-2xl shadow-xs border border-slate-300 overflow-hidden">
        <div class="overflow-x-auto">

          <!-- TEMPLATE PREVIEW FORM 3A -->
          <table v-if="selectedForm === '3A'" class="w-full text-left border-collapse text-xs">
            <thead
              class="bg-slate-50 text-slate-900 border-b border-slate-300 font-bold text-[11px] uppercase tracking-wider text-center">
              <tr class="divide-x divide-slate-300 border-b border-slate-300">
                <th class="p-3 w-12">No. Urut</th>
                <th class="p-3">Satuan Kerja</th>
                <th class="p-3">Register Benda Sitaan Barang Bukti</th>
                <th class="p-3">Register Tahap Penyidikan</th>
                <th class="p-3">Uraian Benda Sitaan Jumlah / Jenis Barang / Ukuran</th>
                <th class="p-3">Tempat Penyimpanan</th>
                <th class="p-3">Identitas Tersangka / Terdakwa</th>
                <th class="p-3">Pasal yang disangkakan / didakwakan</th>
                <th class="p-3">Diselesaikan</th>
                <th class="p-3">Tanggal Pelaksanaan Putusan Hakim & Ijin Jaksa Agung</th>
                <th class="p-3">Keterangan</th>
                <th class="p-3 w-20">Aksi</th>
              </tr>
              <tr
                class="bg-slate-100 divide-x divide-slate-300 text-[10px] text-slate-500 font-semibold border-b border-slate-300">
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
              <template v-for="(item, idx) in cases" :key="item.id">
                <tr v-for="(bb, bIdx) in getBbList(item.barangBuktiList)" :key="bIdx"
                  class="hover:bg-slate-50 transition-colors divide-x divide-slate-200"
                  :class="{ 'border-b-2 border-slate-300': bIdx === getBbList(item.barangBuktiList).length - 1 }">
                  <!-- 1 & 2. Identitas Perkara (Rowspan) -->
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 font-bold text-center align-top">{{ idx + 1 }}</td>
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 font-bold text-center align-top">{{ item.satuanKerja || '-' }}</td>

                  <!-- 3. Reg Sitaan (Rowspan) -->
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 align-top text-center">
                    <div class="flex flex-col items-center text-xs text-slate-800">
                      <div>{{ item.noRegBendaSitaan || '-' }}</div>
                      <div v-if="item.tglPenerimaan && item.tglPenerimaan !== '-'"
                        class="mt-1 text-[11px] text-slate-500">
                        {{ item.tglPenerimaan }}
                      </div>
                    </div>
                  </td>

                  <!-- 4. Reg Penyidikan (Rowspan) -->
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 align-top text-center">
                    <div class="flex flex-col items-center text-xs text-slate-800">
                      <div>{{ item.noRegPenyidikan || '-' }}</div>
                      <div v-if="item.tglRegPenyidikan && item.tglRegPenyidikan !== '-'"
                        class="mt-1 text-[11px] text-slate-500">
                        {{ item.tglRegPenyidikan }}
                      </div>
                    </div>
                  </td>

                  <!-- 5. Uraian Benda Sitaan (Per Barang Bukti Sejajar) -->
                  <td class="p-3 align-top">
                    <div class="text-[11px] text-slate-900 whitespace-normal break-words">
                      - {{ formatJumlahTerbilang(bb.jumlah || bb.jumlahNarkotika || bb.jumlahSatuan || 1) }} {{
                        bb.uraianBarangBukti || bb.namaBarangBukti || bb.jenisBarangBukti || 'BB' }}
                    </div>
                  </td>

                  <!-- 6. Tempat Penyimpanan (Rowspan Dinamis) -->
                  <td v-if="shouldRenderTempatPenyimpanan(item.barangBuktiList, bIdx)"
                    :rowspan="getTempatPenyimpananRowspan(item.barangBuktiList, bIdx)"
                    class="p-3 text-center align-middle">
                    <div class="text-[11px] font-medium text-slate-800">{{ bb.tempatPenyimpanan || '-' }}</div>
                  </td>

                  <!-- 7 - 11. Informasi Perkara (Rowspan) -->
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length" class="p-3 align-top">- {{
                    item.identitasTersangka || '-' }}</td>
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length" class="p-3 align-top">{{
                    item.pasalDisangkakan || item.pasalDidakwakan || '-' }}</td>

                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 text-center align-top">
                    <span v-if="item.statusDiselesaikan && item.statusDiselesaikan !== '-'" class="inline-block">
                      {{ item.statusDiselesaikan }}
                    </span>
                    <span v-else class="text-slate-400 font-bold">-</span>
                  </td>

                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 text-center align-top">{{ item.tglPelaksanaanPutusan || '-' }}</td>
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 text-slate-700 align-top text-center">{{ item.keterangan || '-' }}</td>

                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 text-center align-top">
                    <div class="flex items-center justify-center gap-2">
                      <button class="p-2 rounded-md hover:bg-blue-200 hover:text-blue-700" type="button"
                        @click="editCase(item.id, (item as any).case_index)">
                        <Edit3 class="w-4 h-4 hover:text-blue-500" />
                      </button>
                      <button class="p-2 rounded-md hover:bg-red-200 hover:text-red-700" type="button"
                        @click="deleteCase(item.id, (item as any).case_index)">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>

          <!-- TEMPLATE PREVIEW FORM 3B -->
          <table v-if="selectedForm === '3B'" class="w-full text-left border-collapse text-xs">
            <thead
              class="bg-slate-50 text-slate-900 border-b border-slate-300 font-bold text-[11px] uppercase tracking-wider text-center">
              <tr class="divide-x divide-slate-300 border-b border-slate-300">
                <th class="p-3 w-12">No. Urut</th>
                <th class="p-3">Kejaksaan</th>
                <th class="p-3">Sisa Bulan Lalu</th>
                <th class="p-3">Masuk Bulan Laporan</th>
                <th class="p-3">Jumlah Bulan Laporan</th>
                <th class="p-3">Sisa Bulan Laporan</th>
                <th class="p-3">Keterangan</th>
                <th class="p-3 w-20">Aksi</th>
              </tr>
              <tr
                class="bg-slate-100 divide-x divide-slate-300 text-[10px] text-slate-500 font-semibold border-b border-slate-300">
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
              <tr v-for="(item, idx) in cases" :key="item.id"
                class="hover:bg-slate-50 transition-colors divide-x divide-slate-200 text-center">
                <td class="p-3 font-bold">{{ idx + 1 }}</td>
                <td class="p-3 font-semibold text-left">{{ item.satuanKerja || '-' }}</td>
                <td class="p-3 font-medium">{{ item.sisaBulanLalu || '0' }}</td>
                <td class="p-3 font-medium">{{ item.masukBulanLaporan || '0' }}</td>
                <td class="p-3 font-bold text-blue-700">{{ item.jumlahBulanLaporan || '0' }}</td>
                <td class="p-3 font-medium">{{ item.sisaBulanLaporan || '0' }}</td>
                <td class="p-3 text-slate-600 italic text-left">{{ item.keterangan || '-' }}</td>
                <td class="p-3">
                  <div class="flex items-center justify-center gap-1">
                    <button type="button" @click="editCase(item.id)"
                      class="p-1 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded cursor-pointer">
                      <Edit3 class="w-4 h-4" />
                    </button>
                    <button type="button" @click="deleteCase(item.id)"
                      class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded cursor-pointer">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- TEMPLATE PREVIEW FORM 3C -->
          <table v-if="selectedForm === '3C'" class="w-full text-left border-collapse text-xs">
            <thead
              class="bg-slate-50 text-slate-900 border-b border-slate-300 font-bold text-[11px] uppercase tracking-wider text-center">
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
                <th class="p-3 w-20">Aksi</th>
              </tr>

              <tr
                class="bg-slate-100 divide-x divide-slate-300 text-[10px] text-slate-500 font-semibold border-b border-slate-300">
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
              <template v-for="(item, idx) in cases" :key="item.id">
                <tr v-for="(bb, bIdx) in getBbList(item.barangBuktiList)" :key="bIdx"
                  class="hover:bg-slate-50 transition-colors divide-x divide-slate-200"
                  :class="{ 'border-b-2 border-slate-300': bIdx === getBbList(item.barangBuktiList).length - 1 }">
                  <!-- 1 & 2. Identitas Perkara (Rowspan) -->
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 text-center font-bold align-top">{{ idx + 1 }}</td>
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 font-bold text-center align-top">{{ item.satuanKerja || '-' }}</td>

                  <!-- 3. Jenis Barang Bukti (Per-baris Sejajar) -->
                  <td class="p-3 align-top">
                    <div class="text-[11px] text-slate-800 whitespace-normal break-words">
                      - {{ formatJumlahTerbilang(bb.jumlah) }} {{ bb.jenisBarangBukti || '-' }}
                    </div>
                  </td>

                  <!-- 4 & 5. Pasal & Reg Sitaan (Rowspan) -->
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 align-top text-xs text-slate-800">{{ item.pasalDidakwakan || item.pasalDisangkakan || '-'
                    }}</td>
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 align-top text-center">
                    <div class="flex flex-col items-center text-xs text-slate-800">
                      <div>{{ item.noRegBendaSitaan || '-' }}</div>
                      <div v-if="item.tglPenerimaan && item.tglPenerimaan !== '-'"
                        class="mt-1 text-[11px] text-slate-500">
                        {{ item.tglPenerimaan }}
                      </div>
                    </div>
                  </td>

                  <!-- 6, 7, 8. Rincian Barang Bukti (Per-baris Sejajar) -->
                  <td class="p-3 align-top">
                    <div class="text-[11px] text-slate-800 whitespace-normal break-words">- {{ bb.macamJenisKadar || '-'
                    }}</div>
                  </td>
                  <td class="p-3 align-top">
                    <div class="text-[11px] text-slate-800 whitespace-normal break-words">- {{
                      formatJumlahTerbilang(bb.jumlah) }}</div>
                  </td>
                  <td class="p-3 align-top">
                    <div class="text-[11px] text-slate-800 whitespace-normal break-words">- {{ bb.satuan || '-' }}</div>
                  </td>

                  <!-- 9. Tempat Penyimpanan (Rowspan Dinamis) -->
                  <td v-if="shouldRenderTempatPenyimpanan(item.barangBuktiList, bIdx)"
                    :rowspan="getTempatPenyimpananRowspan(item.barangBuktiList, bIdx)"
                    class="p-3 text-center align-middle">
                    <div class="text-[11px] font-medium text-slate-800">{{ bb.tempatPenyimpanan || '-' }}</div>
                  </td>

                  <!-- 10. Keputusan Pengadilan (Rowspan) -->
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 align-top text-center">
                    <div class="flex flex-col items-center text-xs text-slate-800">
                      <div>{{ item.noKepPengadilan || '-' }}</div>
                      <div v-if="item.tglKepPengadilan && item.tglKepPengadilan !== '-'"
                        class="mt-1 text-[11px] text-slate-500">
                        {{ item.tglKepPengadilan }}
                      </div>
                    </div>
                  </td>

                  <!-- 11. Amar Putusan dengan Logika "Sda" -->
                  <td class="p-3 align-top">
                    <div class="text-[11px] text-slate-800 whitespace-normal break-words">
                      <!-- Jika data sama persis dengan baris di atasnya, tampilkan '- Sda' saja -->
                      <template v-if="isSdaAmarPutusan(item.barangBuktiList, bIdx)">
                        - Sda
                      </template>

                      <!-- Jika baris pertama / nilainya berbeda, tampilkan lengkap -->
                      <template v-else>
                        - {{ bb.amarPutusan || '-' }} {{ bb.uraianPutusan ? bb.uraianPutusan : '' }}
                      </template>
                    </div>
                  </td>

                  <!-- 12 & Aksi (Rowspan) -->
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 text-center align-top">{{
                      item.tglPelaksanaanPutusan || '-' }}</td>
                  <td v-if="bIdx === 0" :rowspan="getBbList(item.barangBuktiList).length"
                    class="p-3 text-center align-top">
                    <div class="flex items-center justify-center gap-1">
                      <button type="button" @click="editCase(item.id)"
                        class="p-1 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded cursor-pointer">
                        <Edit3 class="w-4 h-4" />
                      </button>
                      <button type="button" @click="deleteCase(item.id)"
                        class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded cursor-pointer">
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>

          <!-- EMPTY STATE -->
          <div v-if="cases.length === 0" class="p-12 text-center text-slate-400">
            <FileText class="w-10 h-10 mx-auto stroke-1 text-slate-300 mb-2" />
            <p class="text-xs font-bold text-slate-600">Tidak ada laporan data perkara ditemukan pada periode/kategori
              ini.</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Silakan ubah filter tindak pidana, bulan, tahun, atau pilihan
              form di
              atas.</p>
          </div>

        </div>

        <!-- FOOTER PREVIEW -->
        <div class="p-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 bg-slate-50">
          <span>Menampilkan {{ cases.length }} Laporan Form {{ selectedForm }}</span>
          <div class="font-semibold text-slate-700">
            Periode: {{monthOptions.find(m => m.value === selectedMonth)?.label}} {{ selectedYear }}
          </div>
        </div>

      </div>

    </div>
  </AuthenticatedLayout>
</template>