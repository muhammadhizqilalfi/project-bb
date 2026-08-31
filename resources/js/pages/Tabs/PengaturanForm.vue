<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import {
  Sliders,
  Plus,
  Trash2,
  Edit3,
  Search,
  Database,
  Scale,
  Pill,
  Ruler,
  Warehouse,
  Bookmark,
  UserCheck,
  Save,
  X,
  ChevronLeft,
  ChevronRight,
  AlertCircle
} from 'lucide-vue-next';

interface DropdownItem {
  id: string | number;
  category: string;
  label: string;
  formTarget: '3A' | '3C' | '3D' | '3E' | '3F' | 'Keduanya';
  isDefault?: boolean;
}

interface OfficerData {
  jabatan_kasi?: string;
  nama_kasi?: string;
  nip_kasi?: string;
  pangkat_kasi?: string;
}

interface Props {
  optionsData?: DropdownItem[];
  officerData?: OfficerData;
}

const props = withDefaults(defineProps<Props>(), {
  optionsData: () => [],
  officerData: () => ({
    jabatan_kasi: 'KEPALA SEKSI PEMULIHAN ASET DAN PENGELOLAAN BARANG BUKTI',
    nama_kasi: '',
    nip_kasi: '',
    pangkat_kasi: ''
  })
});

const activeMenu = ref('PENGATURAN FORM');
const selectedCategory = ref<string>('kategori_pidana');
const searchQuery = ref<string>('');

// --- STATE PAGINATION ---
const currentPage = ref(1);
const itemsPerPage = ref(10);

watch([selectedCategory, searchQuery], () => {
  currentPage.value = 1;
});

// --- NAVIGASI KATEGORI PENGATURAN TERSEGMENTASI ---
const groupedCategories = [
  {
    groupName: 'GENERAL / UMUM',
    items: [
      { key: 'kategori_pidana', label: 'Kategori Tindak Pidana', icon: Scale, desc: 'Pilihan jenis tindak pidana utama (Form 3A & 3C)' },
      { key: 'jenis_narkotika', label: 'Jenis Narkotika', icon: Pill, desc: 'Rincian spesifik golongan narkotika' },
      { key: 'satuan', label: 'Satuan Barang Bukti', icon: Ruler, desc: 'Satuan unit fisik atau kuantitatif' },
      { key: 'tempat_penyimpanan', label: 'Tempat Penyimpanan', icon: Warehouse, desc: 'Lokasi gudang penitipan / RUPBASAN' },
      { key: 'keterangan_tahap', label: 'Keterangan Tahap Perkara', icon: Bookmark, desc: 'Status tahap penanganan perkara' },
    ]
  },
  {
    groupName: 'FORM 3D (LELANG)',
    items: [
      { key: 'instansi_penilai', label: 'Instansi Penilai', icon: Database, desc: 'Daftar penilai taksiran barang (Form 3D)' },
      { key: 'status_lelang', label: 'Status Hasil Lelang', icon: Database, desc: 'Status lelang barang rampasan (Form 3D)' },
      { key: 'keterangan_3d', label: 'Keterangan Form 3D', icon: Bookmark, desc: 'Opsi keterangan kasus lelang laku' },
    ]
  },
  {
    groupName: 'FORM 3E & 3F (RAMPASAN & PENJUALAN)',
    items: [
      { key: 'keterangan_3e', label: 'Keterangan Form 3E', icon: Bookmark, desc: 'Opsi keterangan lelang rampasan' },
      { key: 'keterangan_3f', label: 'Keterangan Form 3F', icon: Bookmark, desc: 'Opsi keterangan penjualan langsung' },
    ]
  },
  {
    groupName: 'PENGESAHAN & LAPORAN',
    items: [
      { key: 'pejabat_penandatangan', label: 'Penandatangan Laporan', icon: UserCheck, desc: 'Nama, NIP & Jabatan Kepala Seksi' },
    ]
  }
];

const activeCategoryMeta = computed(() => {
  for (const group of groupedCategories) {
    const found = group.items.find(c => c.key === selectedCategory.value);
    if (found) return found;
  }
  return groupedCategories[0].items[0];
});

// Filter Opsi untuk Tabel Master Dropdown
const filteredOptions = computed(() => {
  return props.optionsData.filter(item => {
    const isCategoryMatch = item.category === selectedCategory.value;
    const isSearchMatch = item.label.toLowerCase().includes(searchQuery.value.toLowerCase());
    return isCategoryMatch && isSearchMatch;
  });
});

// Slicing Array Data Berdasarkan Pagination
const paginatedOptions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredOptions.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredOptions.value.length / itemsPerPage.value) || 1;
});

// Modal State & Form Master Dropdown
const isModalOpen = ref(false);
const editingId = ref<string | number | null>(null);

const form = useForm({
  id: null as string | number | null,
  category: selectedCategory.value,
  label: '',
  formTarget: 'Keduanya' as '3A' | '3C' | '3D' | '3E' | '3F' | 'Keduanya',
});

// Form Pejabat Penandatangan
const officerForm = useForm({
  jabatan_kasi: props.officerData?.jabatan_kasi || 'KEPALA SEKSI PEMULIHAN ASET DAN PENGELOLAAN BARANG BUKTI',
  nama_kasi: props.officerData?.nama_kasi || '',
  nip_kasi: props.officerData?.nip_kasi || '',
  pangkat_kasi: props.officerData?.pangkat_kasi || '',
});

const openAddModal = () => {
  editingId.value = null;
  form.clearErrors();
  form.category = selectedCategory.value;
  form.label = '';
  form.formTarget = 'Keduanya';
  isModalOpen.value = true;
};

const openEditModal = (item: DropdownItem) => {
  form.clearErrors();
  form.id = item.id;
  form.category = item.category;
  form.label = item.label;
  form.formTarget = item.formTarget;
  editingId.value = item.id;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

const saveOption = () => {
  form.label = form.label.trim();

  if (!form.label) {
    alert('Label opsi tidak boleh kosong!');
    return;
  }

  form.category = selectedCategory.value;

  if (editingId.value) {
    router.put(`/settings/${editingId.value}`, form.data(), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  } else {
    router.post('/settings', form.data(), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  }
};

const deleteOption = (item: DropdownItem) => {
  if (confirm(`⚠️ PERINGATAN SISTEM INTEGRITAS:\n\nApakah Anda yakin ingin menghapus opsi "${item.label}"?\nMenghapus opsi master dapat memengaruhi visualisasi data pada berkas/laporan lama yang telah diarsipkan.`)) {
    router.delete(`/settings/${item.id}`, {
      preserveScroll: true,
    });
  }
};

const saveOfficerSettings = () => {
  officerForm.post('/settings/officer', {
    preserveScroll: true,
    onSuccess: () => {
      alert('Data Penandatangan Laporan berhasil diperbarui!');
    },
  });
};

const handlePaste = (event: ClipboardEvent, targetObj: any, fieldName: string) => {
  event.preventDefault();
  const pastedText = event.clipboardData?.getData('text');
  if (!pastedText) return;

  const cleanedText = pastedText.replace(/[\r\n]+/g, ' ').replace(/\s+/g, ' ').trim();
  targetObj[fieldName] = cleanedText;
};
</script>

<template>
  <Head title="Pengaturan Master Data & Penandatangan" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-6 bg-[#F4F6F9] min-h-screen">
      
      <!-- PAGE HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
          <div class="flex items-center gap-2 text-slate-800">
            <Sliders class="w-6 h-6 text-emerald-600" />
            <h1 class="text-2xl font-extrabold tracking-tight">Pengaturan System & Master Data</h1>
          </div>
          <p class="text-xs text-slate-500 mt-1">
            Kelola opsi dropdown dinamis serta data pejabat penandatangan laporan resmi.
          </p>
        </div>
      </div>

      <!-- MAIN CONTAINER -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- SIDEBAR NAVIGASI KATEGORI (TERSEGMENTASI) -->
        <div class="lg:col-span-4 bg-white rounded-2xl p-3 border border-slate-200 shadow-xs space-y-4">
          <div v-for="(group, gIndex) in groupedCategories" :key="gIndex" class="space-y-1">
            <!-- HEADER SEGMENTASI -->
            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider px-3 pt-2 pb-1 border-b border-slate-100">
              {{ group.groupName }}
            </p>

            <!-- ITEM MENU -->
            <button
              v-for="cat in group.items"
              :key="cat.key"
              type="button"
              @click="selectedCategory = cat.key; searchQuery = '';"
              :class="[
                'w-full text-left p-3 rounded-xl transition-all flex items-start gap-3 cursor-pointer',
                selectedCategory === cat.key 
                  ? 'bg-[#0E1B2E] text-white shadow-md' 
                  : 'text-slate-700 hover:bg-slate-100'
              ]"
            >
              <component 
                :is="cat.icon" 
                :class="[
                  'w-5 h-5 mt-0.5 shrink-0',
                  selectedCategory === cat.key ? 'text-[#FFD000]' : 'text-slate-500'
                ]" 
              />
              <div>
                <p class="text-xs font-bold leading-snug">{{ cat.label }}</p>
                <p :class="['text-[10px] mt-0.5 line-clamp-1', selectedCategory === cat.key ? 'text-slate-300' : 'text-slate-400']">
                  {{ cat.desc }}
                </p>
              </div>
            </button>
          </div>
        </div>

        <!-- MAIN CONTENT AREA -->
        <div class="lg:col-span-8 space-y-5">
          
          <!-- FORM PENANDATANGAN LAPORAN -->
          <div v-if="selectedCategory === 'pejabat_penandatangan'" class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
              <div class="p-3 bg-amber-50 text-amber-600 rounded-xl border border-amber-100">
                <UserCheck class="w-6 h-6" />
              </div>
              <div>
                <h2 class="text-base font-extrabold text-slate-900">Pejabat Penandatangan Laporan</h2>
                <p class="text-xs text-slate-500">Pengaturan identitas Kepala Seksi yang akan dicantumkan pada lembar pengesahan/cetak PDF.</p>
              </div>
            </div>

            <form @submit.prevent="saveOfficerSettings" class="space-y-4">
              <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                    JABATAN <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="officerForm.jabatan_kasi" 
                    type="text" 
                    required 
                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-[#FFD000] font-semibold"
                  />
                </div>

                <div>
                  <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                    NAMA LENGKAP & GELAR <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="officerForm.nama_kasi" 
                    type="text" 
                    required 
                    class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-[#FFD000] font-semibold"
                  />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                      NIP (NOMOR INDUK PEGAWAI) <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="officerForm.nip_kasi" 
                      type="text" 
                      required 
                      class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-[#FFD000] font-semibold"
                    />
                  </div>

                  <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                      PANGKAT / GOLONGAN
                    </label>
                    <input 
                      v-model="officerForm.pangkat_kasi" 
                      type="text" 
                      class="w-full px-3.5 py-2.5 text-xs bg-white border border-slate-200 rounded-xl text-slate-900 outline-none focus:border-slate-400 focus:ring-2 focus:ring-[#FFD000] font-semibold"
                    />
                  </div>
                </div>
              </div>

              <div class="flex items-center justify-end">
                <button
                  type="submit"
                  :disabled="officerForm.processing"
                  class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-extrabold rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                >
                  <Save class="w-4 h-4 text-[#FFD000]" />
                  <span>Simpan Data Penandatangan</span>
                </button>
              </div>
            </form>
          </div>

          <!-- TABEL MASTER DROPDOWN -->
          <template v-else>
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs space-y-4">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                  <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
                    <component :is="activeCategoryMeta.icon" class="w-6 h-6" />
                  </div>
                  <div>
                    <h2 class="text-base font-extrabold text-slate-900">{{ activeCategoryMeta.label }}</h2>
                    <p class="text-xs text-slate-500">{{ activeCategoryMeta.desc }}</p>
                  </div>
                </div>

                <button
                  type="button"
                  @click="openAddModal"
                  class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer shrink-0"
                >
                  <Plus class="w-4 h-4 text-[#FFD000]" />
                  <span>Tambah Opsi Baru</span>
                </button>
              </div>

              <div class="relative">
                <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari label opsi..."
                  class="w-full pl-10 pr-4 py-2.5 text-xs bg-[#F4F6F8] border border-transparent rounded-xl text-slate-800 outline-none focus:bg-white focus:border-slate-300 transition-all"
                />
              </div>
            </div>

            <!-- TABEL DATA -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
              <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                  <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                      <th class="p-4 w-12 text-center">No</th>
                      <th class="p-4">Label Opsi Dropdown</th>
                      <th class="p-4 w-32 text-center">Digunakan Di</th>
                      <th class="p-4 w-28 text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 text-slate-800">
                    <tr 
                      v-for="(item, index) in paginatedOptions" 
                      :key="item.id"
                      class="hover:bg-slate-50 transition-colors"
                    >
                      <td class="p-4 text-center font-bold text-slate-400">
                        {{ ((currentPage - 1) * itemsPerPage) + index + 1 }}
                      </td>
                      <td class="p-4 font-bold text-slate-900">{{ item.label }}</td>
                      <td class="p-4 text-center">
                        <span 
                          :class="[
                            'px-2.5 py-1 text-[10px] font-bold rounded-full border uppercase',
                            item.formTarget === '3A' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                            item.formTarget === '3C' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                            item.formTarget === '3D' ? 'bg-purple-50 text-purple-700 border-purple-200' :
                            item.formTarget === '3E' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
                            item.formTarget === '3F' ? 'bg-pink-50 text-pink-700 border-pink-200' :
                            'bg-emerald-50 text-emerald-700 border-emerald-200'
                          ]"
                        >
                          {{ item.formTarget }}
                        </span>
                      </td>
                      <td class="p-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                          <button
                            type="button"
                            @click="openEditModal(item)"
                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                            title="Edit Opsi"
                          >
                            <Edit3 class="w-4 h-4" />
                          </button>
                          <button
                            type="button"
                            @click="deleteOption(item)"
                            class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                            title="Hapus Opsi"
                          >
                            <Trash2 class="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>

                    <tr v-if="filteredOptions.length === 0">
                      <td colspan="4" class="p-8 text-center text-slate-400">
                        <AlertCircle class="w-8 h-8 mx-auto mb-2 text-slate-300" />
                        <p class="font-medium">Tidak ada opsi ditemukan.</p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- PAGINATION -->
              <div v-if="filteredOptions.length > 0" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 bg-slate-50/50">
                <div>
                  Menampilkan {{ ((currentPage - 1) * itemsPerPage) + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredOptions.length) }} dari {{ filteredOptions.length }} data
                </div>
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    @click="currentPage--"
                    :disabled="currentPage === 1"
                    class="p-2 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                  >
                    <ChevronLeft class="w-4 h-4" />
                  </button>
                  <span class="font-bold text-slate-700 px-2">
                    Halaman {{ currentPage }} dari {{ totalPages }}
                  </span>
                  <button
                    type="button"
                    @click="currentPage++"
                    :disabled="currentPage === totalPages"
                    class="p-2 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                  >
                    <ChevronRight class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </template>

        </div>
      </div>

      <!-- MODAL FORM DROPDOWN -->
      <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4 border border-slate-100">
          <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-sm font-extrabold text-slate-900">
              {{ editingId ? 'Edit Opsi Dropdown' : 'Tambah Opsi Dropdown Baru' }}
            </h3>
            <button type="button" @click="closeModal" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
              <X class="w-4 h-4" />
            </button>
          </div>

          <form @submit.prevent="saveOption" class="space-y-4">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                LABEL OPSI <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.label"
                type="text"
                required
                @paste="handlePaste($event, form, 'label')"
                placeholder="Masukkan teks opsi..."
                :class="[
                  'w-full px-3.5 py-2.5 text-xs rounded-xl outline-none font-semibold transition-all',
                  form.errors.label
                    ? 'bg-red-50 border-2 border-red-300 text-red-900 focus:ring-2 focus:ring-red-400'
                    : 'bg-[#F4F6F8] border border-transparent text-slate-900 focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000]'
                ]"
              />
              <p v-if="form.errors.label" class="text-[10px] text-red-500 mt-1 font-semibold">
                {{ form.errors.label }}
              </p>
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                TARGET FORMULIR
              </label>
              <select
                v-model="form.formTarget"
                class="w-full px-3.5 py-2.5 text-xs bg-[#F4F6F8] border border-transparent rounded-xl text-slate-900 font-semibold outline-none focus:bg-white focus:border-slate-300"
              >
                <option value="Keduanya">Semua Form (Keduanya/Universal)</option>
                <option value="3A">Khusus Form 3A</option>
                <option value="3C">Khusus Form 3C</option>
                <option value="3D">Khusus Form 3D</option>
                <option value="3E">Khusus Form 3E</option>
                <option value="3F">Khusus Form 3F</option>
              </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
              <button 
                type="button" 
                @click="closeModal" 
                class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 bg-[#0E1B2E] hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50"
              >
                {{ editingId ? 'Simpan Perubahan' : 'Tambah Opsi' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>