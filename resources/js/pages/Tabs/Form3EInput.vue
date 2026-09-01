<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';

interface Props {
  formId: number;
  caseIndex?: number;
  caseData?: any;
  month: number;
  year: number;
  dropdownOptions?: {
    keterangan_options?: string[];
  };
}

const props = defineProps<Props>();
const activeMenu = ref('FORM');

const isEdit = props.caseIndex !== undefined;

const form = useForm({
  satuanKerja: props.caseData?.satuanKerja || 'Kejari Banda Aceh',
  terpidana_nama: props.caseData?.terpidana_nama || '',
  putusan_no: props.caseData?.putusan_no || '',
  putusan_tgl: props.caseData?.putusan_tgl || '',
  items: props.caseData?.items || [
    { nama_barang: '', jumlah: 1, satuan: 'Pcs', harga_jual: 0 }
  ],
  tgl_penjualan: props.caseData?.tgl_penjualan || '',
  ntpn: props.caseData?.ntpn || '',
  keterangan: props.caseData?.keterangan || '',
});

const addItem = () => {
  form.items.push({ nama_barang: '', jumlah: 1, satuan: 'Pcs', harga_jual: 0 });
};

const removeItem = (index: number) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1);
  }
};

const submit = () => {
  if (isEdit) {
    form.put(`/form3e/${props.formId}/cases/${props.caseIndex}`);
  } else {
    form.post(`/form3e/${props.formId}/cases`);
  }
};
</script>

<template>
  <Head :title="isEdit ? 'Edit Kasus Form 3E' : 'Tambah Kasus Form 3E'" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-6 bg-[#F4F6F9] min-h-screen">
      
      <!-- HEADER -->
      <div class="flex items-center justify-between pb-2 border-b border-slate-200">
        <div class="flex items-center gap-3">
          <button type="button" @click="router.get('/form3e')" class="p-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 transition-colors cursor-pointer">
            <ArrowLeft class="w-4 h-4 text-slate-700" />
          </button>
          <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
              {{ isEdit ? 'Edit Kasus Form 3E' : 'Tambah Kasus Form 3E' }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Form Induk Periode: Bulan {{ props.month }} / {{ props.year }}</p>
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- INFORMASI UTAMA -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b pb-2">Informasi Penjualan Lelang (Form 3E)</h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Satuan Kerja / Kejari</label>
              <input v-model="form.satuanKerja" type="text" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 font-medium" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Nama Terpidana</label>
              <input v-model="form.terpidana_nama" type="text" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Nomor Putusan</label>
              <input v-model="form.putusan_no" type="text" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal Putusan</label>
              <input v-model="form.putusan_tgl" type="date" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>
          </div>
        </div>

        <!-- RINCIAN BARANG & HARGA JUAL PER ITEM -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center justify-between border-b pb-2">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Rincian Barang Rampasan & Harga Jual</h2>
            <button type="button" @click="addItem" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 cursor-pointer">
              <Plus class="w-3.5 h-3.5" />
              <span>Tambah Barang</span>
            </button>
          </div>

          <div v-for="(item, idx) in form.items" :key="idx" class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3 relative text-xs">
            <div class="flex items-center justify-between font-bold text-slate-600">
              <span>Barang #{{ Number(idx) + 1 }}</span>
              <button v-if="form.items.length > 1" type="button" @click="removeItem(Number(idx))" class="text-red-500 hover:text-red-700 cursor-pointer">
                <Trash2 class="w-4 h-4" />
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
              <div class="md:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Nama / Uraian Barang Rampasan</label>
                <input v-model="item.nama_barang" type="text" class="w-full bg-white border border-slate-300 rounded-lg p-2" required />
              </div>

              <div>
                <label class="block font-semibold text-slate-700 mb-1">Jumlah & Satuan</label>
                <div class="flex gap-2">
                  <input v-model.number="item.jumlah" type="number" min="0" class="w-1/2 bg-white border border-slate-300 rounded-lg p-2" placeholder="Jml" />
                  <input v-model="item.satuan" type="text" class="w-1/2 bg-white border border-slate-300 rounded-lg p-2" placeholder="Satuan" />
                </div>
              </div>

              <div>
                <label class="block font-semibold text-slate-700 mb-1">Harga Jual Item (Rp)</label>
                <input v-model.number="item.harga_jual" type="number" min="0" class="w-full bg-white border border-slate-300 rounded-lg p-2 font-bold text-emerald-700" required />
              </div>
            </div>
          </div>
        </div>

        <!-- INFORMASI PENYETORAN & ADMINISTRASI -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b pb-2">Informasi Penjualan & Penyetoran</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal Penjualan</label>
              <input v-model="form.tgl_penjualan" type="date" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Nomor NTPN</label>
              <input v-model="form.ntpn" type="text" placeholder="Masukkan NTPN..." class="w-full border border-slate-300 rounded-lg p-2.5" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Keterangan</label>
              <select v-model="form.keterangan" class="border border-slate-300 rounded-lg p-2.5 bg-slate-50 w-full font-semibold">
                <option value="">-- Pilih Keterangan --</option>
                <option v-for="opt in props.dropdownOptions?.keterangan_options" :key="opt" :value="opt">
                  {{ opt }}
                </option>
              </select>
            </div>

          </div>
        </div>

        <!-- TOMBOL AKSI -->
        <div class="flex items-center justify-end gap-3 pt-2">
          <button type="button" @click="router.get('/form3e')" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-100 cursor-pointer">
            Batal
          </button>
          <button type="submit" :disabled="form.processing" class="px-5 py-2.5 rounded-xl bg-[#FFD000] hover:bg-yellow-400 text-slate-950 text-xs font-extrabold flex items-center gap-2 border border-amber-300 cursor-pointer">
            <Save class="w-4 h-4" />
            <span>Simpan Data Kasus</span>
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>