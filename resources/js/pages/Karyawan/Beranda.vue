<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
  Scale,
  FileCheck,
  BarChart2,
  TrendingUp,
  Filter,
  Download,
  Eye,
  Edit3,
  AlertTriangle,
  Archive
} from 'lucide-vue-next';

// Reactive Data State
const stats = ref({
  totalCases: '1,095',
  pendingReports: '35'
});

const previewReports = ref([
  {
    formCode: 'FORM 3A',
    formTitle: 'EVIDENCE INVENTORY',
    period: 'OCT 2023',
    status: 'DONE',
    icon: Eye
  },
  {
    formCode: 'FORM 3B',
    formTitle: 'ASSET SEIZURE',
    period: 'OCT 2023',
    status: 'DONE',
    icon: Edit3
  },
  {
    formCode: 'FORM 3C',
    formTitle: 'DISPOSAL STATUS',
    period: 'OCT 2023',
    status: 'DRAFT',
    icon: AlertTriangle
  },
  {
    formCode: 'FORM 3A',
    formTitle: 'EVIDENCE INVENTORY',
    period: 'NOV 2023',
    status: 'DRAFT',
    icon: Archive
  }
]);

const ongoingCases = ref([
  {
    id: 'CASE-2023-9982',
    title: 'Penyitaan Aset Tindak Pidana Korupsi - Wilayah IV',
    btnPrimary: 'VIEW DETAILS',
    btnSecondary: 'UPDATE'
  },
  {
    id: 'CASE-2023-1004',
    title: 'Pelimpahan Barang Bukti Narkotika Golongan I',
    btnPrimary: 'VIEW DETAILS',
    btnSecondary: 'UPDATE'
  },
  {
    id: 'CASE-2023-8821',
    title: 'Pemusnahan Barang Bukti Miras & Kosmetik Ilegal',
    btnPrimary: 'FIX NOW',
    btnSecondary: 'HISTORY'
  }
]);
</script>

<template>
  <Head title="Beranda - Karyawan" />
  <AuthenticatedLayout 
    userRole="karyawan" 
    activeMenu="BERANDA"
    userName="Jaksa Utama Muda"
    nip="NIP. 19820412 200501 1 002"
  >
    <div class="p-8 space-y-6">
      
      <!-- Baris Atas: 2 Metric Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Total Cases -->
        <div class="bg-white rounded-xl p-6 shadow-xs border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
              TOTAL CASES THIS MONTH
            </p>
            <div class="flex items-baseline gap-2 mt-2">
              <span class="text-3xl font-extrabold text-slate-900 leading-none">
                {{ stats.totalCases }}
              </span>
              <span class="text-xs font-bold text-gray-400 uppercase">CASES</span>
            </div>
          </div>
          <div class="p-3 bg-gray-50/80 rounded-xl text-gray-300">
            <Scale class="w-9 h-9" />
          </div>
        </div>

        <!-- Card 2: Pending Reports -->
        <div class="bg-white rounded-xl p-6 shadow-xs border border-gray-100 flex items-center justify-between">
          <div>
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
              PENDING REPORTS TO ATTORNEY GENERAL
            </p>
            <div class="flex items-baseline gap-2 mt-2">
              <span class="text-3xl font-extrabold text-slate-900 leading-none">
                {{ stats.pendingReports }}
              </span>
              <span class="text-xs font-bold text-gray-400 uppercase">SUBMISSIONS</span>
            </div>
          </div>
          <div class="p-3 bg-gray-50/80 rounded-xl text-gray-300">
            <FileCheck class="w-9 h-9" />
          </div>
        </div>
      </div>

      <!-- Baris Bawah: Grid 2 Kolom (8 Kolom Table + 4 Kolom Tracker) -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Kolom Kiri: Preview Laporan 3A, 3B, 3C (8 Columns) -->
        <div class="lg:col-span-8 bg-white rounded-xl shadow-xs border border-gray-100 p-6 flex flex-col justify-between">
          <div>
            <!-- Header Kartu -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
              <div class="flex items-center gap-2.5">
                <BarChart2 class="w-5 h-5 text-gray-500" />
                <h2 class="text-sm font-bold text-slate-800 tracking-wide">
                  Preview Laporan 3A, 3B, 3C
                </h2>
              </div>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="bg-[#2D3748] hover:bg-slate-800 text-white text-[11px] font-bold px-3.5 py-2 rounded-lg flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                  <Download class="w-3.5 h-3.5" />
                  <span>EXPORT PDF</span>
                </button>
                <button
                  type="button"
                  class="p-2 border border-gray-200 hover:bg-gray-50 rounded-lg text-gray-500 transition-colors cursor-pointer"
                >
                  <Filter class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Tabel -->
            <div class="overflow-x-auto mt-2">
              <table class="w-full text-left border-collapse">
                <thead>
                  <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                    <th class="py-3 px-3">FORM TYPE</th>
                    <th class="py-3 px-3">PERIOD</th>
                    <th class="py-3 px-3">STATUS</th>
                    <th class="py-3 px-3 text-center">ACTION</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr
                    v-for="(item, index) in previewReports"
                    :key="index"
                    class="hover:bg-slate-50/50 transition-colors"
                  >
                    <td class="py-3.5 px-3">
                      <div class="font-bold text-slate-900 text-xs">
                        {{ item.formCode }}
                      </div>
                      <div class="text-[10px] text-gray-400 font-semibold tracking-tight">
                        {{ item.formTitle }}
                      </div>
                    </td>
                    <td class="py-3.5 px-3 text-xs font-semibold text-gray-600">
                      {{ item.period }}
                    </td>
                    <td class="py-3.5 px-3">
                      <span
                        :class="[
                          'inline-block text-[10px] font-bold px-2.5 py-0.5 rounded tracking-wide',
                          item.status === 'DONE'
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-gray-200 text-gray-600'
                        ]"
                      >
                        {{ item.status }}
                      </span>
                    </td>
                    <td class="py-3.5 px-3 text-center">
                      <button
                        type="button"
                        class="p-1.5 hover:bg-gray-100 rounded text-gray-500 transition-colors cursor-pointer"
                      >
                        <component :is="item.icon" class="w-4 h-4" />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Footer Tabel & Pagination -->
          <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400 font-medium">
            <span>Showing 4 of 24 monthly reports</span>
            <div class="flex items-center gap-2 font-bold text-gray-600">
              <button type="button" class="px-2 py-1 hover:text-slate-900 cursor-pointer">PREV</button>
              <span class="px-2 py-1 text-slate-900 font-extrabold">1</span>
              <button type="button" class="px-2 py-1 hover:text-slate-900 cursor-pointer">NEXT</button>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Ongoing Process Tracker (4 Columns) -->
        <div class="lg:col-span-4 bg-white rounded-xl shadow-xs border border-gray-100 p-6 flex flex-col justify-between">
          <div>
            <!-- Header Kartu -->
            <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100">
              <TrendingUp class="w-5 h-5 text-gray-500" />
              <h2 class="text-sm font-bold text-slate-800 tracking-wide">
                Ongoing Process Tracker
              </h2>
            </div>

            <!-- List Card Kasus -->
            <div class="space-y-3 mt-4">
              <div
                v-for="c in ongoingCases"
                :key="c.id"
                class="bg-[#F8FAFC] border-l-4 border-[#FFD000] rounded-r-lg p-4 transition-all"
              >
                <div class="font-extrabold text-xs text-slate-900 tracking-wider">
                  {{ c.id }}
                </div>
                <p class="text-xs text-slate-600 font-medium mt-1 leading-snug">
                  {{ c.title }}
                </p>
                <div class="flex items-center gap-2 mt-3">
                  <button
                    type="button"
                    class="bg-black hover:bg-slate-800 text-white text-[10px] font-extrabold py-1.5 px-3.5 rounded transition-colors cursor-pointer"
                  >
                    {{ c.btnPrimary }}
                  </button>
                  <button
                    type="button"
                    class="bg-gray-200 hover:bg-gray-300 text-slate-800 text-[10px] font-extrabold py-1.5 px-3.5 rounded transition-colors cursor-pointer"
                  >
                    {{ c.btnSecondary }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </AuthenticatedLayout>
</template>