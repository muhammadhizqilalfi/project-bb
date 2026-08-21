<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import {
  Calendar,
  Layers
} from 'lucide-vue-next';

interface Form3BItem {
  id?: string;
  satuanKerja?: string;
  kategoriTindakPidana?: string;
  sisaBulanLalu?: number;
  masukBulanLaporan?: number;
  jumlahBulanLaporan?: number;
  perkaraSelesai?: number;
  sisaBulanLaporan?: number;
  keterangan?: string;
}

interface Props {
  filters?: {
    month: number;
    year: number;
    kategori: string;
  };
  cases?: Form3BItem[];
  calculatedData?: Form3BItem;
}

const props = defineProps<Props>();

const activeMenu = ref('FORM');
const page = usePage();

// State Filter (Hanya Bulan dan Tahun)
const selectedMonth = ref<number>(props.filters?.month || new Date().getMonth() + 1);
const selectedYear = ref<number>(props.filters?.year || new Date().getFullYear());

// Opsi Bulan
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

// Menangkap Data dari Props cases (Array) atau fallback
const casesList = computed<Form3BItem[]>(() => {
  if (props.cases && props.cases.length > 0) {
    return props.cases;
  }
  if (props.calculatedData) {
    return [props.calculatedData];
  }
  return [
    {
      satuanKerja: 'Kejari Banda Aceh',
      kategoriTindakPidana: '-',
      sisaBulanLalu: 0,
      masukBulanLaporan: 0,
      jumlahBulanLaporan: 0,
      perkaraSelesai: 0,
      sisaBulanLaporan: 0,
      keterangan: '-'
    }
  ];
});

// Trigger Filter Otomatis
const applyFilter = () => {
  router.get('/form3b', {
    kategori: 'ALL', // Selalu mengambil semua kategori tindak pidana
    month: selectedMonth.value,
    year: selectedYear.value,
  }, {
    preserveState: true,
    preserveScroll: true
  });
};
</script>

<template>
  <Head title="Form 3B - Penanganan Perkara" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-6 bg-[#F4F6F9] min-h-screen">
      
      <!-- FILTER BAR (Hanya Periode Bulan & Tahun) -->
      <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200/80 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2.5">
          <Calendar class="w-4 h-4 text-slate-500 shrink-0" />
          <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">PERIODE LAPORAN:</span>
          <div class="h-4 w-px bg-slate-200"></div>
          
          <select 
            v-model="selectedMonth" 
            @change="applyFilter" 
            class="bg-slate-50 hover:bg-slate-100 border border-slate-200/80 text-xs font-bold text-slate-800 rounded-xl px-3 py-2 outline-none cursor-pointer transition-colors focus:ring-2 focus:ring-slate-300"
          >
            <option v-for="m in monthOptions" :key="m.value" :value="m.value">
              {{ m.label }}
            </option>
          </select>

          <select 
            v-model="selectedYear" 
            @change="applyFilter" 
            class="bg-slate-50 hover:bg-slate-100 border border-slate-200/80 text-xs font-bold text-slate-800 rounded-xl px-3 py-2 outline-none cursor-pointer transition-colors focus:ring-2 focus:ring-slate-300"
          >
            <option v-for="y in yearOptions" :key="y" :value="y">
              {{ y }}
            </option>
          </select>
        </div>
      </div>

      <!-- MAIN TABLE CARD -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden space-y-4 px-6 py-4">
        
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="p-2 bg-slate-100 text-slate-800 rounded-lg">
              <Layers class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-extrabold text-slate-900">
                Laporan Rekapitulasi Form 3B
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Kategori: <span class="font-bold text-slate-800">Semua Tindak Pidana</span>
              </p>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 text-slate-900 font-bold border-b border-slate-300 text-[11px] uppercase tracking-wider text-center">
              <tr class="divide-x divide-slate-300">
                <th class="p-3.5 w-16">No. Urut</th>
                <th class="p-3.5 text-left w-72">Tindak Pidana</th>
                <th class="p-3.5 w-40">Sisa Bulan Lalu</th>
                <th class="p-3.5 w-44 bg-blue-50/50 text-blue-900">Masuk Bulan Laporan</th>
                <th class="p-3.5 w-44 bg-slate-100/60">Jumlah Bulan Laporan</th>
                <th class="p-3.5 w-40 bg-emerald-50/50 text-emerald-900">Selesai (Form 3C)</th>
                <th class="p-3.5 w-44 bg-amber-50/50 text-amber-900">Sisa Bulan Laporan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 text-slate-800">
              <tr 
                v-for="(item, idx) in casesList" 
                :key="item.id || idx" 
                class="hover:bg-slate-50 transition-colors divide-x divide-slate-200 text-center"
              >
                <td class="p-4 font-bold text-slate-500">{{ idx + 1 }}.</td>
                <!-- KOLOM TINDAK PIDANA -->
                <td class="p-4 font-extrabold text-slate-900 text-left">
                  {{ item.kategoriTindakPidana || '-' }}
                  <span class="block text-[10px] font-normal text-slate-500">
                    {{ item.satuanKerja || 'Kejari Banda Aceh' }}
                  </span>
                </td>
                <td class="p-4 font-extrabold text-slate-700 bg-slate-50/30">
                  {{ item.sisaBulanLalu ?? 0 }}
                </td>
                <td class="p-4 font-black text-blue-700 bg-blue-50/20">
                  {{ item.masukBulanLaporan ?? 0 }}
                </td>
                <td class="p-4 font-black text-slate-900 bg-slate-100/30">
                  {{ item.jumlahBulanLaporan ?? 0 }}
                </td>
                <td class="p-4 font-black text-emerald-700 bg-emerald-50/20">
                  {{ item.perkaraSelesai ?? 0 }}
                </td>
                <td class="p-4 font-black text-amber-800 bg-amber-50/30">
                  <span class="px-3 py-1 rounded-full bg-amber-100 border border-amber-300">
                    {{ item.sisaBulanLaporan ?? 0 }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 bg-slate-50/60 border-t border-slate-200/80 text-[11px] text-slate-500 flex justify-between items-center">
          <span>Menampilkan {{ casesList.length }} baris rekapitulasi data perkara Form 3B.</span>
          <span class="font-semibold text-slate-700">Terintegrasi otomatis dengan Form 3A & Form 3C</span>
        </div>

      </div>

    </div>
  </AuthenticatedLayout>
</template>