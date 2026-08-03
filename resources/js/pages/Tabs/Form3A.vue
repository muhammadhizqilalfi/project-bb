<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
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

const showDeleteModal = ref(false);
const selectedForm = ref<FormItem | null>(null);

const askDeleteForm = (form: FormItem) => {
  selectedForm.value = form;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  selectedForm.value = null;
};

const confirmDeleteForm = () => {
  if (!selectedForm.value) return;

  router.delete(`/forms/3a/${selectedForm.value.id}`, {
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
  <Head title="Form 3A" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">FORM 3A</h1>
          <p class="text-xs text-slate-500 mt-1">Kelola form bulanan sebelum menambahkan data case.</p>
        </div>
        
        <!-- Tombol mengarah langsung ke Wizard Halaman Baru -->
        <button
          type="button"
          class="bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 rounded-lg flex items-center gap-2 transition-colors cursor-pointer"
          @click="router.get('/form3a/create-wizard')"
        >
          <Plus class="w-4 h-4" />
          <span>Buat Form</span>
        </button>
      </div>

      <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
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
                Belum ada form. Buatkan form baru terlebih dahulu.
              </td>
            </tr>
            <tr v-for="form in forms" :key="form.id" class="border-t border-slate-100 hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ form.name }}</td>
              <td class="px-6 py-4 text-sm text-slate-600">{{ getMonthLabel(form.month) }} {{ form.year }}</td>
              <td class="px-6 py-4">
                <div class="flex items-center justify-end gap-2">
                  <button
                    type="button"
                    class="flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900"
                    @click="router.get(`/form3a/${form.id}/cases/create`)"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Tambah Case</span>
                  </button>
                  <button
                    type="button"
                    class="w-8 h-8 rounded-md border border-red-200 text-red-600 hover:text-red-700 hover:bg-red-50 flex items-center justify-center cursor-pointer"
                    @click="askDeleteForm(form)"
                    title="Hapus Form"
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

    <!-- Modal Delete -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
      <div class="w-full max-w-sm rounded-xl bg-white border border-slate-200 shadow-xl p-5 space-y-4">
        <h2 class="text-sm font-bold text-slate-900">Hapus Form</h2>
        <p class="text-sm text-slate-600">
          Yakin ingin menghapus <span class="font-semibold text-slate-900">{{ selectedForm?.name }}</span>?
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