<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';
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
  forms: {
    type: Array as PropType<FormItem[]>,
    default: () => []
  }
});

const monthOptions = [
  { value: 1, label: 'Januari' },
  { value: 2, label: 'Februari' },
  { value: 3, label: 'Maret' },
  { value: 4, label: 'April' },
  { value: 5, label: 'Mei' },
  { value: 6, label: 'Juni' },
  { value: 7, label: 'Juli' },
  { value: 8, label: 'Agustus' },
  { value: 9, label: 'September' },
  { value: 10, label: 'Oktober' },
  { value: 11, label: 'November' },
  { value: 12, label: 'Desember' }
];

const yearOptions = computed(() => {
  const currentYear = new Date().getFullYear();

  return Array.from({ length: 10 }, (_, index) => currentYear + 2 - index);
});

const showCreateModal = ref(false);
const showDeleteModal = ref(false);
const selectedForm = ref<FormItem | null>(null);

const createFormState = ref({
  name: '',
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear()
});

const openCreateModal = () => {
  showCreateModal.value = true;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  createFormState.value = {
    name: '',
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear()
  };
};

const submitCreateForm = () => {
  router.post('/forms/3c', createFormState.value, {
    preserveScroll: true,
    onSuccess: () => {
      closeCreateModal();
    }
  });
};

const askDeleteForm = (form: FormItem) => {
  selectedForm.value = form;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedForm.value = null;
};

const confirmDeleteForm = () => {
  if (!selectedForm.value) {
    return;
  }

  router.delete(`/forms/3c/${selectedForm.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      closeDeleteModal();
    }
  });
};

const getMonthLabel = (month: number) => {
  const found = monthOptions.find((option) => option.value === month);

  return found ? found.label : '-';
};
</script>

<template>
  <Head title="Form 3C" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 max-w-7xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">FORM 3C</h1>
          <p class="text-xs text-slate-500 mt-1">Kelola form bulanan sebelum menambahkan data case.</p>
        </div>
        <button
          type="button"
          class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer"
          @click="openCreateModal"
        >
          <Plus class="w-4 h-4" />
          <span>Tambah Form</span>
        </button>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th class="text-left px-6 py-3 text-xs font-bold text-slate-600 uppercase tracking-wider">Nama Form</th>
              <th class="text-left px-6 py-3 text-xs font-bold text-slate-600 uppercase tracking-wider">Periode</th>
              <th class="text-right px-6 py-3 text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="forms.length === 0">
              <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-500">
                Belum ada form. Tambahkan form baru terlebih dahulu.
              </td>
            </tr>
            <tr v-for="form in forms" :key="form.id" class="border-t border-slate-100">
              <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ form.name }}</td>
              <td class="px-6 py-4 text-sm text-slate-600">{{ getMonthLabel(form.month) }} {{ form.year }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button
                    type="button"
                    class="w-8 h-8 rounded-md border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center justify-center cursor-pointer"
                    @click="router.get(`/form3c/${form.id}/edit`)"
                  >
                    <Pencil class="w-4 h-4" />
                  </button>
                  <button
                    type="button"
                    class="w-8 h-8 rounded-md border border-red-200 text-red-600 hover:text-red-700 hover:bg-red-50 flex items-center justify-center cursor-pointer"
                    @click="askDeleteForm(form)"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-md rounded-xl bg-white border border-slate-200 shadow-xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
          <h2 class="text-sm font-bold text-slate-900">Buat Form 3C</h2>
          <button type="button" class="text-slate-500 hover:text-slate-700 cursor-pointer" @click="closeCreateModal">
            <X class="w-4 h-4" />
          </button>
        </div>
        <form class="p-5 space-y-4" @submit.prevent="submitCreateForm">
          <div>
            <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Nama Form</label>
            <input
              v-model="createFormState.name"
              type="text"
              required
              placeholder="Contoh: FORM 3C Juli 2026"
              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#FFD000] focus:border-slate-400 outline-none"
            />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Bulan</label>
              <select
                v-model="createFormState.month"
                required
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#FFD000] focus:border-slate-400 outline-none"
              >
                <option v-for="month in monthOptions" :key="month.value" :value="month.value">{{ month.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">Tahun</label>
              <select
                v-model="createFormState.year"
                required
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#FFD000] focus:border-slate-400 outline-none"
              >
                <option v-for="year in yearOptions" :key="year" :value="year">{{ year }}</option>
              </select>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 pt-1">
            <button
              type="button"
              class="px-3.5 py-2 text-xs font-bold border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-100 cursor-pointer"
              @click="closeCreateModal"
            >
              Batal
            </button>
            <button
              type="submit"
              class="px-3.5 py-2 text-xs font-bold bg-[#0E1B2E] text-white rounded-lg hover:bg-slate-800 cursor-pointer"
            >
              Simpan Form
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-sm rounded-xl bg-white border border-slate-200 shadow-xl p-5 space-y-4">
        <h2 class="text-sm font-bold text-slate-900">Hapus Form</h2>
        <p class="text-sm text-slate-600">
          Yakin ingin menghapus
          <span class="font-semibold text-slate-900">{{ selectedForm?.name }}</span>?
        </p>
        <div class="flex items-center justify-end gap-2">
          <button
            type="button"
            class="px-3.5 py-2 text-xs font-bold border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-100 cursor-pointer"
            @click="closeDeleteModal"
          >
            Batal
          </button>
          <button
            type="button"
            class="px-3.5 py-2 text-xs font-bold bg-red-600 text-white rounded-lg hover:bg-red-700 cursor-pointer"
            @click="confirmDeleteForm"
          >
            Hapus
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
