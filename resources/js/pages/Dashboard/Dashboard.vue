<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import {
  Scale,
  CheckCircle2,
  Plus,
  Edit3,
  PieChart as PieIcon,
  ArrowRight,
  Building2,
  Calendar,
  Table as TableIcon,
  Filter
} from 'lucide-vue-next';

// Chart.js Imports[cite: 4]
import {
  Chart,
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend,
  Title
} from 'chart.js'

Chart.register(
  DoughnutController,
  ArcElement,
  Tooltip,
  Legend,
  Title
);

interface NarkotikaItem {
  nama: string;
  jumlah: number;
  satuan: string;
}

interface Props {
  filters?: {
    month: number;
    year: number;
  };
  stats?: {
    totalPerkaraMasuk3A: number;
    totalPerkaraSelesai3C: number;
    narkotikaSummary?: NarkotikaItem[];
  };
  categoryChartData?: {
    labels: string[];
    data: number[];
  };
  recentCases?: Array<{
    id: string;
    caseIndex: number;
    noRegSitaan: string;
    tersangka: string;
    kategori: string;
    tempatPenyimpanan: string;
    formType: '3A' | '3C';
  }>;
  rekapitulasiMatriks?: Array<{
    kategori: string;
    masuk: string;
    sisaBulanLalu: number | string;
    selesai: string;
    dikembalikan: string;
    dimusnahkan: string;
    lelang: string;
  }>;
  currentMonthName?: string;
  currentYear?: number;
}

const props = defineProps<Props>();

const activeMenu = ref('BERANDA');
const page = usePage();
const currentUser = computed(() => page.props.auth?.user || { name: 'Muadz Fauzi', role: 'Pengelola Barang Bukti' });

// State Filter Bulan & Tahun[cite: 4]
const selectedMonth = ref<number>(props.filters?.month || new Date().getMonth() + 1);
const selectedYear = ref<number>(props.filters?.year || new Date().getFullYear());

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

// Trigger Reload saat Filter Bulan/Tahun Berubah[cite: 4]
const applyFilters = () => {
  router.get('/dashboard', {
    month: selectedMonth.value,
    year: selectedYear.value,
  }, {
    preserveState: true,
    preserveScroll: true
  });
};

// Stat Data[cite: 4]
const statsData = computed(() => ({
  totalPerkaraMasuk3A: props.stats?.totalPerkaraMasuk3A ?? 0,
  totalPerkaraSelesai3C: props.stats?.totalPerkaraSelesai3C ?? 0,
}));

// Ambil daftar narkotika dinamis[cite: 2]
const narkotikaList = computed(() => props.stats?.narkotikaSummary || []);

// Mengecek apakah ada perkara narkotika yang masuk pada periode ini[cite: 2, 4]
const hasNarkotikaThisMonth = computed(() => {
  return narkotikaList.value.length > 0;
});

// Canvas Ref untuk Donut Chart[cite: 4]
const categoryChartRef = ref<HTMLCanvasElement | null>(null);
let categoryChartInstance: Chart | null = null;

const renderChart = () => {
  if (!categoryChartRef.value) return;

  if (categoryChartInstance) {
    categoryChartInstance.destroy();
  }

  const labels = props.categoryChartData?.labels || [
    'NARKOTIKA DAN ZAT ADIKTIF',
    'OHARDA',
    'KAMNEGTIBUM DAN TPUL',
    'KORUPSI',
    'TERORIS'
  ];

  const chartValues = props.categoryChartData?.data || [0, 0, 0, 0, 0];

  categoryChartInstance = new Chart(categoryChartRef.value, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [
        {
          data: chartValues,
          backgroundColor: [
            '#10B981', // Emerald[cite: 4]
            '#3B82F6', // Blue[cite: 4]
            '#F59E0B', // Amber[cite: 4]
            '#EF4444', // Red[cite: 4]
            '#8B5CF6'  // Purple[cite: 4]
          ],
          borderWidth: 2,
          borderColor: '#ffffff'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { 
            font: { size: 10, weight: 'bold' }, 
            padding: 12,
            boxWidth: 12
          }
        }
      },
      cutout: '65%'
    }
  });
};

onMounted(() => {
  renderChart();
});

watch(() => props.categoryChartData, () => {
  renderChart();
}, { deep: true });

const navigateTo = (path: string) => {
  router.get(path);
};

const editCase = (formType: string, id: string, index?: number) => {
  const query = index !== undefined ? `?index=${index}` : '';
  router.get(`/form${formType.toLowerCase()}/${id}/edit${query}`);
};
</script>

<template>
  <Head title="Beranda Utama" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-8 bg-[#F4F6F9] min-h-screen">
      
      <!-- BANNER UCAPAN SELAMAT DATANG & FILTER PERIODE BULAN/TAHUN -->
      <div class="bg-[#0E1B2E] rounded-2xl p-6 text-white shadow-lg border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="space-y-2 z-10">
          <div class="flex flex-wrap items-center gap-2">
            <span class="bg-[#FFD000] text-slate-950 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider">
              Sistem Informasi Barang Bukti
            </span>

            <!-- DROPDOWN FILTER BULAN & TAHUN -->
            <div class="flex items-center gap-1.5 bg-slate-800/90 p-1 rounded-lg border border-slate-700/80">
              <Calendar class="w-3.5 h-3.5 text-amber-400 ml-1.5" />
              <select 
                v-model="selectedMonth" 
                @change="applyFilters" 
                class="bg-transparent text-white border-none text-xs font-bold focus:ring-0 outline-none cursor-pointer py-0.5 pl-1 pr-6"
              >
                <option v-for="m in monthOptions" :key="m.value" :value="m.value" class="bg-slate-900 text-white">
                  {{ m.label }}
                </option>
              </select>
              <select 
                v-model="selectedYear" 
                @change="applyFilters" 
                class="bg-transparent text-white border-none text-xs font-bold focus:ring-0 outline-none cursor-pointer py-0.5 pl-1 pr-6"
              >
                <option v-for="y in yearOptions" :key="y" :value="y" class="bg-slate-900 text-white">
                  {{ y }}
                </option>
              </select>
            </div>
          </div>

          <h1 class="text-2xl font-black tracking-tight text-white flex items-center gap-2">
            Selamat Datang, {{ currentUser.name }}!
          </h1>
          <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
            Pusat kendali rekapitulasi data benda sitaan dan barang bukti Kejaksaan. Pantau status perkara, akumulasi kuantitas, dan cetak laporan bulanan secara terintegrasi.
          </p>
        </div>

        <!-- Tombol Quick Action Bar -->
        <div class="flex flex-wrap items-center gap-2 z-10 shrink-0">
          <button 
            @click="navigateTo('/form3a/create')" 
            type="button"
            class="bg-slate-800 hover:bg-[#FFD000] text-white hover:text-slate-950 text-xs font-bold px-4 py-2.5 rounded-xl border border-slate-700 flex items-center gap-2 transition-all cursor-pointer"
          >
            <Plus class="w-4 h-4 stroke-[3]" />
            <span>Form 3A</span>
          </button>
          <button 
            @click="navigateTo('/form3c/create')" 
            type="button"
            class="bg-slate-800 hover:bg-[#FFD000] text-white hover:text-slate-950 text-xs font-bold px-4 py-2.5 rounded-xl border border-slate-700 flex items-center gap-2 transition-all cursor-pointer"
          >
            <Plus class="w-4 h-4" />
            <span>Form 3C</span>
          </button>
        </div>

        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
      </div>

      <!-- 2 METRIC CARDS KATEGORI MASUK DAN SELESAI -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Card 1: Perkara Masuk Bulan Ini (Form 3A) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-blue-800 uppercase">FORM 3A</span>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">PERKARA MASUK ({{ currentMonthName || 'Agustus' }} {{ currentYear || 2026 }})</p>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
              <span class="text-4xl font-black text-slate-900 leading-none">{{ statsData.totalPerkaraMasuk3A }}</span>
              <span class="text-xs font-bold text-slate-500 uppercase">Perkara Baru</span>
            </div>
          </div>
          <div class="p-4 bg-blue-50 text-blue-600 rounded-2xl border border-blue-100">
            <Scale class="w-7 h-7" />
          </div>
        </div>

        <!-- Card 2: Perkara Selesai / Putus (Form 3C) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex items-center justify-between">
          <div>
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800 uppercase">FORM 3C</span>
              <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">PERKARA SELESAI ({{ currentMonthName || 'Agustus' }} {{ currentYear || 2026 }})</p>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
              <span class="text-4xl font-black text-slate-900 leading-none">{{ statsData.totalPerkaraSelesai3C }}</span>
              <span class="text-xs font-bold text-emerald-600 uppercase">Dieksekusi</span>
            </div>
          </div>
          <div class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100">
            <CheckCircle2 class="w-7 h-7" />
          </div>
        </div>

      </div>

      <!-- WIDGET RINGKASAN NARKOTIKA DINAMIS -->
      <div 
        v-if="hasNarkotikaThisMonth" 
        class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4"
      >
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2.5">
            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
            <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">
              Rekapitulasi Kuantitas Narkotika Masuk Periode Ini
            </h2>
          </div>
          <span class="text-[11px] text-slate-400 font-medium">Periode: {{ currentMonthName || 'Agustus' }} {{ currentYear || 2026 }}</span>
        </div>

        <!-- GRID RENDER OTOMATIS BERDASARKAN SELURUH JENIS NARKOTIKA -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div 
            v-for="(item, idx) in narkotikaList" 
            :key="idx" 
            class="bg-[#F8FAFC] rounded-xl p-4 border border-slate-200/60 flex items-center gap-4"
          >
            <div class="p-3 bg-emerald-100 text-emerald-700 rounded-xl font-black text-xs uppercase shrink-0">
              {{ item.nama }}
            </div>
            <div>
              <p class="text-[10px] font-bold text-slate-400 uppercase">Total {{ item.nama }}</p>
              <p class="text-xl font-black text-slate-900">
                {{ item.jumlah.toLocaleString('id-ID') }} 
                <span class="text-xs font-normal text-slate-500">{{ item.satuan }}</span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- GRID BERSAMPINGAN: GRAFIK KATEGORI (KIRI) & PERKARA TERBARU (KANAN) -->
      <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch">
        
        <!-- CARD KIRI: GRAFIK CHART KATEGORI TINDAK PIDANA (5 COLS) -->
        <div class="xl:col-span-5 bg-white rounded-2xl p-6 border border-slate-200 shadow-xs flex flex-col justify-between space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
              <PieIcon class="w-5 h-5 text-slate-700" />
              <h3 class="text-sm font-extrabold text-slate-900">Persentase Kategori Tindak Pidana</h3>
            </div>
          </div>

          <div class="h-64 w-full relative flex items-center justify-center py-2">
            <canvas ref="categoryChartRef"></canvas>
          </div>
        </div>

        <!-- CARD KANAN: TABEL PERKARA TERBARU (7 COLS) -->
        <div class="xl:col-span-7 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col justify-start">
          <div class="p-6  border-b border-slate-100 flex items-center justify-between">
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Perkara Terbaru</h3>
              <p class="text-xs text-slate-500 mt-0.5">Daftar perkara terdaftar pada periode {{ currentMonthName }} {{ currentYear }}.</p>
            </div>

            <button 
              @click="navigateTo('/laporan')" 
              type="button"
              class="text-xs font-bold text-slate-700 hover:text-slate-950 flex items-center gap-1 cursor-pointer"
            >
              <span>Lihat Semua Laporan</span>
              <ArrowRight class="w-4 h-4" />
            </button>
          </div>

          <div class="overflow-x-auto px-6 py-4">
            <table class="w-full text-left border-collapse text-xs">
              <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                <tr>
                  <th class="p-3">No. Reg Sitaan</th>
                  <th class="p-3">Tersangka</th>
                  <th class="p-3">Kategori</th>
                  <th class="p-3 text-center">Form</th>
                  <th class="p-3 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-800">
                <tr v-for="c in (recentCases || [])" :key="c.id" class="hover:bg-slate-50 transition-colors">
                  <td class="p-3 font-bold text-slate-900">{{ c.noRegSitaan }}</td>
                  <td class="p-3 font-medium">{{ c.tersangka }}</td>
                  <td class="p-3 font-semibold text-slate-600 truncate max-w-[150px]">{{ c.kategori }}</td>
                  
                  <td class="p-3 text-center">
                    <span 
                      :class="[
                        'inline-flex items-center justify-center whitespace-nowrap px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider border transition-all',
                        c.formType === '3A' 
                          ? 'bg-blue-50 text-blue-700 border-blue-200 ring-1 ring-blue-400/20 shadow-2xs' 
                          : 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-1 ring-emerald-400/20 shadow-2xs'
                      ]"
                    >
                      Form {{ c.formType }}
                    </span>
                  </td>

                  <td class="p-3 text-center">
                    <button 
                      @click="editCase(c.formType, c.id, c.caseIndex)" 
                      type="button" 
                      class="p-1.5 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                      title="Edit Perkara Ini"
                    >
                      <Edit3 class="w-4 h-4" />
                    </button>
                  </td>
                </tr>

                <tr v-if="!recentCases || recentCases.length === 0">
                  <td colspan="5" class="p-6 text-center text-slate-400">
                    Tidak ada perkara terdaftar pada periode {{ currentMonthName }} {{ currentYear }}.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- TABEL MATRIKS REKAPITULASI PERKARA & BARANG BUKTI (PALING BAWAH) -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden space-y-4">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
              <TableIcon class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">Rekapitulasi Perkara & Barang Bukti</h3>
              <p class="text-xs text-slate-500 mt-0.5">Matriks rekapitulasi jumlah perkara dan unit barang bukti per kategori (Format: Perkara / Unit).</p>
            </div>
          </div>

          <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
            Periode: {{ currentMonthName || 'Agustus' }} {{ currentYear || 2026 }}
          </span>
        </div>

        <div class="overflow-x-auto px-6 py-4">
          <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 text-slate-900 font-bold border-b border-slate-300 text-[11px] uppercase tracking-wider text-center">
              <tr class="divide-x divide-slate-300 border-b border-slate-300">
                <th rowspan="2" class="p-3 w-56 text-left">Perkara / Unit</th>
                <th rowspan="2" class="p-3 w-32">Masuk</th>
                <th rowspan="2" class="p-3 w-32">Sisa Bulan Lalu</th>
                <th colspan="4" class="p-2 bg-slate-100">Status Penyelesaian</th>
              </tr>
              <tr class="divide-x divide-slate-300 bg-slate-100/70 text-[10px] text-slate-700">
                <th class="p-2.5 w-32">Selesai</th>
                <th class="p-2.5">Dikembalikan</th>
                <th class="p-2.5">Dimusnahkan</th>
                <th class="p-2.5">Lelang</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-800">
              <tr 
                v-for="(row, idx) in (rekapitulasiMatriks || [])" 
                :key="idx" 
                class="hover:bg-slate-50/80 transition-colors divide-x divide-slate-200 text-center"
              >
                <!-- Kategori Perkara -->
                <td class="p-3.5 font-bold text-left text-slate-900 bg-slate-50/30">
                  {{ row.kategori }}
                </td>
                
                <!-- Masuk (Perkara / Unit) -->
                <td class="p-3.5 font-extrabold text-blue-700">
                  {{ row.masuk }}
                </td>

                <!-- Sisa Bulan Lalu -->
                <td class="p-3.5 font-semibold text-slate-700">
                  {{ row.sisaBulanLalu }}
                </td>

                <!-- Selesai (Perkara / Unit) -->
                <td class="p-3.5 font-extrabold text-emerald-700 bg-emerald-50/20">
                  {{ row.selesai }}
                </td>

                <!-- Dikembalikan -->
                <td class="p-3.5 font-medium text-slate-800">
                  {{ row.dikembalikan }}
                </td>

                <!-- Dimusnahkan -->
                <td class="p-3.5 font-medium text-slate-800">
                  {{ row.dimusnahkan }}
                </td>

                <!-- Lelang -->
                <td class="p-3.5 font-medium text-slate-800">
                  {{ row.lelang }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>