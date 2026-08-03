<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { PropType } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';

const activeMenu = ref('FORM');

type FormItem = {
  id: string;
  name: string;
  month: number;
  year: number;
  latestCase?: {
    kejaksaan: string;
    kategoriTindakPidana: string;
    jenisBarangBukti: string;
    noRegBendaSitaan: string;
    jumlahSatuan: string;
    jenisSatuan: string;
    amarPutusan: string;
    savedAt: string | null;
  } | null;
};

defineProps({
  form: {
    type: Object as PropType<FormItem>,
    required: true
  }
});

const monthNames = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const getMonthLabel = (month: number) => monthNames[month - 1] || '-';

const page = usePage<{ flash?: { success?: string } }>();
const successMessage = computed(() => page.props.flash?.success || '');
</script>

<template>
  <Head title="Edit Form 3C" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <!-- Kontainer dilebarkan (w-full) -->
    <div class="p-8 w-full mx-auto space-y-6">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-[-10px] opacity-0"
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-[-10px] opacity-0"
      >
        <div
          v-if="successMessage"
          class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700"
        >
          {{ successMessage }}
        </div>
      </Transition>

      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Form 3C</h1>
          <p class="text-xs text-slate-500 mt-1">Kelola form sebelum menambahkan data case.</p>
        </div>
        <button
          type="button"
          class="text-xs font-bold text-slate-700 border border-slate-300 rounded-lg px-3.5 py-2 hover:bg-slate-100 flex items-center gap-1.5 cursor-pointer"
          @click="router.get('/form3c')"
        >
          <ArrowLeft class="w-4 h-4" />
          <span>Kembali</span>
        </button>
      </div>

      <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex gap-12">
          <div>
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Form</p>
            <p class="text-sm font-semibold text-slate-900 mt-1">{{ form.name }}</p>
          </div>
          <div>
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Periode</p>
            <p class="text-sm font-semibold text-slate-700 mt-1">{{ getMonthLabel(form.month) }} {{ form.year }}</p>
          </div>
        </div>
        <button
          type="button"
          class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer"
          @click="router.get(`/form3c/${form.id}/cases/create`)"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Case Baru</span>
        </button>
      </div>

      <!-- Preview Case dalam Format Tabel -->
      <div v-if="form.latestCase" class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
        <h2 class="text-sm font-bold text-slate-900">Preview Case Terakhir (Form 3C)</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse border border-slate-300">
            <thead class="bg-slate-800 text-white text-[10px] uppercase tracking-wider">
              <tr>
                <th class="p-3 border border-slate-700">Kejaksaan / Satker</th>
                <th class="p-3 border border-slate-700">No. Register Sitaan</th>
                <th class="p-3 border border-slate-700">Jenis Barang Bukti</th>
                <th class="p-3 border border-slate-700 text-center">Jumlah & Satuan</th>
                <th class="p-3 border border-slate-700">Amar Putusan</th>
              </tr>
            </thead>
            <tbody class="text-xs bg-white text-slate-700">
              <tr>
                <td class="p-3 border border-slate-300">
                  <div class="font-bold text-slate-900">{{ form.latestCase.kejaksaan }}</div>
                  <div class="text-[10px] text-slate-500 mt-1">{{ form.latestCase.kategoriTindakPidana }}</div>
                </td>
                <td class="p-3 border border-slate-300 font-medium">{{ form.latestCase.noRegBendaSitaan || '-' }}</td>
                <td class="p-3 border border-slate-300">{{ form.latestCase.jenisBarangBukti }}</td>
                <td class="p-3 border border-slate-300 text-center font-bold text-slate-900">
                  {{ form.latestCase.jumlahSatuan }} {{ form.latestCase.jenisSatuan }}
                </td>
                <td class="p-3 border border-slate-300">
                  <div class="font-semibold text-slate-800 line-clamp-2" :title="form.latestCase.amarPutusan">{{ form.latestCase.amarPutusan || '-' }}</div>
                  <div class="text-[10px] text-slate-400 mt-2">Disimpan: {{ form.latestCase.savedAt || '-' }}</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>