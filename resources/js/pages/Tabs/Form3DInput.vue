<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import { Plus, Trash2, ArrowLeft, Save } from 'lucide-vue-next';

interface Props {
  formId: number;
  caseIndex?: number;
  caseData?: any;
  month: number;
  year: number;
  dropdownOptions?: {
    status_lelang?: string[];
    instansi_penilai?: string[];
    keterangan_options?: string[];
  };
}

const props = defineProps<Props>();
const activeMenu = ref('FORM');

const isEdit = props.caseIndex !== undefined;

const form = useForm({
  satuanKerja: props.caseData?.satuanKerja || 'Kejari Banda Aceh',
  terpidana_nama: props.caseData?.terpidana_nama || '',
  tgl_penyerahan: props.caseData?.tgl_penyerahan || '',
  putusan_no: props.caseData?.putusan_no || '',
  putusan_tgl: props.caseData?.putusan_tgl || '',
  perkara: props.caseData?.perkara || '',
  items: props.caseData?.items || [
    {
      nama_barang: '',
      harga_taksiran: 0,
      instansi_penilai: props.dropdownOptions?.instansi_penilai?.[0] || 'KPKNL',
      tgl_penilaian: '',
      nilai_laku: 0,
      status_lelang: props.dropdownOptions?.status_lelang?.[0] || 'BELUM_LAKU',
      keterangan: '',
    }
  ],
});

const addItem = () => {
  form.items.push({
    nama_barang: '',
    harga_taksiran: 0,
    instansi_penilai: props.dropdownOptions?.instansi_penilai?.[0] || 'KPKNL',
    tgl_penilaian: '',
    nilai_laku: 0,
    status_lelang: props.dropdownOptions?.status_lelang?.[0] || 'BELUM_LAKU',
    keterangan: '',
  });
};

const removeItem = (index: number) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1);
  }
};

const submit = () => {
  if (isEdit) {
    form.put(`/form3d/${props.formId}/cases/${props.caseIndex}`);
  } else {
    form.post(`/form3d/${props.formId}/cases`);
  }
};
</script>

<template>
  <Head :title="isEdit ? 'Edit Kasus Form 3D' : 'Tambah Kasus Form 3D'" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-6 bg-[#F4F6F9] min-h-screen">
      
      <!-- HEADER -->
      <div class="flex items-center justify-between pb-2 border-b border-slate-200">
        <div class="flex items-center gap-3">
          <button type="button" @click="router.get('/form3d')" class="p-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 transition-colors cursor-pointer">
            <ArrowLeft class="w-4 h-4 text-slate-700" />
          </button>
          <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">
              {{ isEdit ? 'Edit Kasus Form 3D' : 'Tambah Kasus Form 3D' }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Form Induk Periode: Bulan {{ props.month }} / {{ props.year }}</p>
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- INFORMASI PERKARA & TERPIDANA -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b pb-2">
            Informasi Perkara & Terpidana
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Kejari / Satuan Kerja</label>
              <input v-model="form.satuanKerja" type="text" class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 font-medium" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Nama Terpidana</label>
              <input v-model="form.terpidana_nama" type="text" placeholder="Contoh: Budi bin Ahmad" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal Penyerahan dari Satker ke PPA</label>
              <input v-model="form.tgl_penyerahan" type="date" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Perkara</label>
              <input v-model="form.perkara" type="text" placeholder="Contoh: Narkotika / TPUL" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Nomor Putusan Inkraht</label>
              <input v-model="form.putusan_no" type="text" placeholder="Contoh: 123/Pid.B/2026/PN Bna" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Tanggal Putusan Inkraht</label>
              <input v-model="form.putusan_tgl" type="date" class="w-full border border-slate-300 rounded-lg p-2.5" required />
            </div>
          </div>
        </div>

        <!-- RINCIAN BARANG RAMPASAN, PENILAIAN, & HASIL LELANG -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
          <div class="flex items-center justify-between border-b pb-2">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
              Rincian Barang Rampasan, Penilaian, & Lelang
            </h2>
            <button type="button" @click="addItem" class="bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold px-3 py-1.5 rounded-lg flex items-center gap-1.5 cursor-pointer">
              <Plus class="w-3.5 h-3.5" />
              <span>Tambah Barang Rampasan</span>
            </button>
          </div>

          <div v-for="(item, idx) in form.items" :key="idx" class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-3 relative text-xs">
            <div class="flex items-center justify-between font-bold text-slate-700 border-b pb-2">
              <span>Item Barang Rampasan #{{ Number(idx) + 1 }}</span>
              <button v-if="form.items.length > 1" type="button" @click="removeItem(Number(idx))" class="text-red-500 hover:text-red-700 cursor-pointer flex items-center gap-1 text-[11px]">
                <Trash2 class="w-4 h-4" />
                <span>Hapus Item</span>
              </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <!-- Kolom IX -->
              <div class="md:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Nama/Jenis Jumlah Barang Rampasan</label>
                <input v-model="item.nama_barang" type="text" placeholder="Contoh: 1 (satu) unit Mobil Honda HRV Warna Hitam" class="w-full bg-white border border-slate-300 rounded-lg p-2" required />
              </div>

              <!-- Kolom XI -->
              <div>
                <label class="block font-semibold text-slate-700 mb-1">Nama Instansi Penilai</label>
                <select v-model="item.instansi_penilai" class="w-full bg-white border border-slate-300 rounded-lg p-2" required>
                  <option value="" disabled>-- Pilih Instansi --</option>
                  <option v-for="opt in props.dropdownOptions?.instansi_penilai" :key="opt" :value="opt">
                    {{ opt }}
                  </option>
                </select>
              </div>

              <!-- Kolom X -->
              <div>
                <label class="block font-semibold text-slate-700 mb-1">Harga Taksiran / Limit Rp</label>
                <input v-model.number="item.harga_taksiran" type="number" min="0" placeholder="0" class="w-full bg-white border border-slate-300 rounded-lg p-2" />
              </div>

              <!-- Kolom XII -->
              <div>
                <label class="block font-semibold text-slate-700 mb-1">Tanggal Penilaian Terakhir</label>
                <input v-model="item.tgl_penilaian" type="date" class="w-full bg-white border border-slate-300 rounded-lg p-2" />
              </div>

              <!-- Kolom XIII -->
              <div>
                <label class="block font-semibold text-slate-700 mb-1">Nilai Laku Lelang Rp</label>
                <input v-model.number="item.nilai_laku" type="number" min="0" placeholder="Isi 0 jika belum laku" class="w-full bg-white border border-slate-300 rounded-lg p-2" />
              </div>

              <!-- Kolom XIV -->
              <div>
                <label class="block font-semibold text-slate-700 mb-1">Status Lelang</label>
                <select v-model="item.status_lelang" class="w-full bg-white border border-slate-300 rounded-lg p-2" required>
                  <option value="" disabled>-- Pilih Status --</option>
                  <option v-for="opt in props.dropdownOptions?.status_lelang" :key="opt" :value="opt">
                    {{ opt === 'BELUM_LAKU' ? 'BELUM LAKU / BELUM DILELANG' : opt }}
                  </option>
                </select>
              </div>

              <!-- Kolom XV -->
              <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Keterangan</label>
                <select v-model="item.keterangan" class="border border-slate-300 rounded-lg p-2 bg-white w-full font-medium">
                  <option value="">-- Pilih Keterangan --</option>
                  <option v-for="opt in props.dropdownOptions?.keterangan_options" :key="opt" :value="opt">
                    {{ opt }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- BUTTON ACTION -->
        <div class="flex items-center justify-end gap-3">
          <button type="button" @click="router.get('/form3d')" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-100 cursor-pointer">
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