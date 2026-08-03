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
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember'
];

const getMonthLabel = (month: number) => monthNames[month - 1] || '-';

const page = usePage<{ flash?: { success?: string } }>();
const successMessage = computed(() => page.props.flash?.success || '');
</script>

<template>
  <Head title="Edit Form 3C" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 max-w-4xl mx-auto space-y-6">
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

      <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="space-y-3">
          <div>
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Form</p>
            <p class="text-sm font-semibold text-slate-900 mt-1">{{ form.name }}</p>
          </div>
          <div>
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Periode</p>
            <p class="text-sm text-slate-700 mt-1">{{ getMonthLabel(form.month) }} {{ form.year }}</p>
          </div>
        </div>

        <div class="pt-6">
          <button
            type="button"
            class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer"
            @click="router.get(`/form3c/${form.id}/cases/create`)"
          >
            <Plus class="w-4 h-4" />
            <span>Tambah Case</span>
          </button>
        </div>
      </div>

      <div v-if="form.latestCase" class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-sm font-bold text-slate-900 mb-3">Ringkasan Case Terakhir</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
          <div><span class="text-slate-500">Kejaksaan:</span> <span class="font-medium text-slate-900">{{ form.latestCase.kejaksaan }}</span></div>
          <div><span class="text-slate-500">Kategori:</span> <span class="font-medium text-slate-900">{{ form.latestCase.kategoriTindakPidana }}</span></div>
          <div><span class="text-slate-500">Jenis BB:</span> <span class="font-medium text-slate-900">{{ form.latestCase.jenisBarangBukti }}</span></div>
          <div><span class="text-slate-500">No Reg:</span> <span class="font-medium text-slate-900">{{ form.latestCase.noRegBendaSitaan }}</span></div>
          <div><span class="text-slate-500">Jumlah:</span> <span class="font-medium text-slate-900">{{ form.latestCase.jumlahSatuan }} {{ form.latestCase.jenisSatuan }}</span></div>
          <div><span class="text-slate-500">Saved At:</span> <span class="font-medium text-slate-900">{{ form.latestCase.savedAt || '-' }}</span></div>
        </div>
        <div class="mt-3 text-sm">
          <span class="text-slate-500">Amar Putusan:</span>
          <p class="font-medium text-slate-900 mt-1">{{ form.latestCase.amarPutusan }}</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
