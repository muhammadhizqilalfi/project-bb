<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, Plus } from 'lucide-vue-next';
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
</script>

<template>
  <Head title="Edit Form 3A" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 max-w-4xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Form 3A</h1>
          <p class="text-xs text-slate-500 mt-1">Kelola form sebelum menambahkan data case.</p>
        </div>
        <button
          type="button"
          class="text-xs font-bold text-slate-700 border border-slate-300 rounded-lg px-3.5 py-2 hover:bg-slate-100 flex items-center gap-1.5 cursor-pointer"
          @click="router.get('/form3a')"
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
            @click="router.get(`/form3a/${form.id}/cases/create`)"
          >
            <Plus class="w-4 h-4" />
            <span>Tambah Case</span>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
