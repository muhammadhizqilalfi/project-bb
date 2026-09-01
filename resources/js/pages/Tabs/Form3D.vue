<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import { Plus, Trash2, Calendar } from 'lucide-vue-next';

interface Form3DRecord {
  id: number;
  month: number;
  year: number;
  cases?: any[];
}

interface Props {
  forms: Form3DRecord[];
}

const props = defineProps<Props>();
const activeMenu = ref('FORM');
const isCreateFormOpen = ref(false);

const monthNames = [
  '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const newForm = useForm({
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
});

const submitNewForm = () => {
  newForm.post('/form3d/store-form', {
    onSuccess: () => { 
      isCreateFormOpen.value = false;
      newForm.reset();
    }
  });
};

const deleteForm = (formId: number) => {
  if (confirm('Apakah Anda yakin ingin menghapus form bulanan ini?')) {
    router.delete(`/form3d/${formId}/delete-form`, {
      preserveScroll: true,
    });
  }
};

const addCase = (formId: number) => {
  router.get(`/form3d/${formId}/cases/create`);
};
</script>

<template>
  <Head title="FORM 3D" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-6 bg-[#F4F6F9] min-h-screen">
      
      <!-- HEADER PERSIS SEPERTI CONTOH -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-black text-slate-900 tracking-tight">FORM 3D</h1>
          <p class="text-xs text-slate-500 mt-0.5">Kelola form bulanan sebelum menambahkan data case.</p>
        </div>

        <button type="button" @click="isCreateFormOpen = !isCreateFormOpen"
          class="bg-[#111827] hover:bg-slate-800 text-white text-xs font-bold px-4 py-2.5 rounded-xl flex items-center gap-2 transition-all shadow-xs cursor-pointer w-fit">
          <Plus class="w-4 h-4 stroke-[2.5]" />
          <span>Buat Form</span>
        </button>
      </div>

      <!-- MODAL INPUT FORM INDUK -->
      <div v-if="isCreateFormOpen" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
          <Calendar class="w-4 h-4 text-amber-500" />
          <span>Pilih Periode Laporan Form 3D Baru</span>
        </h2>
        <form @submit.prevent="submitNewForm" class="flex flex-wrap items-end gap-4 text-xs">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Bulan</label>
            <select v-model="newForm.month" class="border border-slate-300 rounded-lg p-2.5 bg-slate-50 w-44 font-semibold">
              <option v-for="(m, idx) in monthNames.slice(1)" :key="idx" :value="idx + 1">{{ m }}</option>
            </select>
          </div>
          <div>
            <label class="block font-bold text-slate-700 mb-1">Tahun</label>
            <input v-model.number="newForm.year" type="number" min="2000" max="2100" class="border border-slate-300 rounded-lg p-2.5 bg-slate-50 w-32 font-semibold" />
          </div>
          <button type="submit" :disabled="newForm.processing" class="bg-slate-900 text-white font-bold px-5 py-2.5 rounded-lg hover:bg-slate-800 cursor-pointer">
            Simpan Form Induk
          </button>
          <button type="button" @click="isCreateFormOpen = false" class="text-slate-500 font-bold px-3 py-2.5 hover:text-slate-800">
            Batal
          </button>
        </form>
      </div>

      <!-- TABEL CONTAINER PERSIS SEPERTI CONTOH -->
      <div class="bg-white rounded-2xl shadow-2xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-white text-slate-500 border-b border-slate-200 font-bold uppercase tracking-wider text-[11px]">
              <tr>
                <th class="p-4 pl-6">NAMA FORM</th>
                <th class="p-4">PERIODE</th>
                <th class="p-4 pr-6 text-right w-44">AKSI</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-800">
              <tr v-for="form in forms" :key="form.id" class="hover:bg-slate-50/50 transition-colors">
                <td class="p-4 pl-6 font-bold text-slate-900">
                  FORM 3D {{ monthNames[form.month] }} {{ form.year }}
                </td>
                <td class="p-4 font-normal text-slate-600">
                  {{ monthNames[form.month] }} {{ form.year }}
                </td>
                <td class="p-4 pr-6 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="addCase(form.id)" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-3 py-1.5 rounded-lg font-medium flex items-center gap-1.5 cursor-pointer transition-all shadow-2xs">
                      <Plus class="w-3.5 h-3.5 text-slate-500 stroke-[2.5]" />
                      <span>Tambah Case</span>
                    </button>
                    <button type="button" @click="deleteForm(form.id)" class="p-1.5 bg-white hover:bg-red-50 text-red-500 border border-slate-200 hover:border-red-200 rounded-lg cursor-pointer transition-all" title="Hapus Form">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="forms.length === 0">
                <td colspan="3" class="p-12 text-center text-slate-400">
                  <p class="text-xs font-semibold text-slate-600">Belum ada form bulanan yang dibuat.</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>