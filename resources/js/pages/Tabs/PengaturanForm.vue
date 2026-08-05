<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/Layout.vue';
import {
  Sliders,
  Plus,
  Trash2,
  Edit3,
  Search,
  CheckCircle2,
  AlertCircle,
  Database,
  Scale,
  Pill,
  Ruler,
  Warehouse,
  Bookmark,
  X
} from 'lucide-vue-next';

interface DropdownItem {
  id: string | number;
  category: string;
  label: string;
  formTarget: '3A' | '3C' | 'Keduanya';
  isDefault?: boolean;
}

interface Props {
  optionsData?: DropdownItem[];
}

const props = withDefaults(defineProps<Props>(), {
  optionsData: () => [
    // Default Fallback Data (Jika belum terkoneksi penuh dengan API Backend)
    { id: 1, category: 'kategori_pidana', label: 'KAMNEGTIBUM DAN TPUL', formTarget: '3A' },
    { id: 2, category: 'kategori_pidana', label: 'NARKOTIKA DAN ZAT ADITIF LAINNYA', formTarget: 'Keduanya' },
    { id: 3, category: 'kategori_pidana', label: 'OHARDA', formTarget: '3A' },
    { id: 4, category: 'kategori_pidana', label: 'TERORIS', formTarget: '3A' },
    { id: 5, category: 'kategori_pidana', label: 'KORUPSI', formTarget: 'Keduanya' },
    
    { id: 6, category: 'jenis_narkotika', label: 'Sabu', formTarget: '3A' },
    { id: 7, category: 'jenis_narkotika', label: 'Ganja', formTarget: '3A' },
    { id: 8, category: 'jenis_narkotika', label: 'Ekstasi / Pil', formTarget: '3A' },
    { id: 9, category: 'jenis_narkotika', label: 'Heroin', formTarget: '3A' },
    { id: 10, category: 'jenis_narkotika', label: 'Tembakau Sintetis', formTarget: '3A' },

    { id: 11, category: 'satuan', label: 'Gram', formTarget: '3A' },
    { id: 12, category: 'satuan', label: 'Kilogram (Kg)', formTarget: '3A' },
    { id: 13, category: 'satuan', label: 'Pcs / Butir', formTarget: 'Keduanya' },
    { id: 14, category: 'satuan', label: 'Unit', formTarget: 'Keduanya' },

    { id: 15, category: 'tempat_penyimpanan', label: 'Gudang Barang Bukti Kejaksaan Negeri Banda Aceh', formTarget: 'Keduanya' },
    { id: 16, category: 'tempat_penyimpanan', label: 'RUPBASAN Banda Aceh', formTarget: 'Keduanya' },

    { id: 17, category: 'keterangan_tahap', label: 'Tahap Persidangan', formTarget: '3A' },
    { id: 18, category: 'keterangan_tahap', label: 'Tahap II', formTarget: '3A' },
    { id: 19, category: 'keterangan_tahap', label: 'Tahap Pelimpahan', formTarget: '3A' },
  ]
});

const activeMenu = ref('PENGATURAN FORM');
const selectedCategory = ref<string>('kategori_pidana');
const searchQuery = ref<string>('');

// Category Meta Navigation
const categories = [
  { key: 'kategori_pidana', label: 'Kategori Tindak Pidana', icon: Scale, desc: 'Pilihan jenis tindak pidana utama (Form 3A & 3C)' },
  { key: 'jenis_narkotika', label: 'Jenis Narkotika', icon: Pill, desc: 'Rincian spesifik golongan narkotika' },
  { key: 'satuan', label: 'Satuan Barang Bukti', icon: Ruler, desc: 'Satuan unit fisik atau kuantitatif' },
  { key: 'tempat_penyimpanan', label: 'Tempat Penyimpanan', icon: Warehouse, desc: 'Lokasi gudang penitipan / RUPBASAN' },
  { key: 'keterangan_tahap', label: 'Keterangan Tahap Perkara', icon: Bookmark, desc: 'Status tahap penanganan perkara' },
];

const activeCategoryMeta = computed(() => {
  return categories.find(c => c.key === selectedCategory.value) || categories[0];
});

// Filtered Options
const filteredOptions = computed(() => {
  return props.optionsData.filter(item => {
    const isCategoryMatch = item.category === selectedCategory.value;
    const isSearchMatch = item.label.toLowerCase().includes(searchQuery.value.toLowerCase());
    return isCategoryMatch && isSearchMatch;
  });
});

// Modal State & Form
const isModalOpen = ref(false);
const editingId = ref<string | number | null>(null);

const form = useForm({
  category: selectedCategory.value,
  label: '',
  formTarget: 'Keduanya' as '3A' | '3C' | 'Keduanya',
});

const openAddModal = () => {
  editingId.value = null;
  form.category = selectedCategory.value;
  form.label = '';
  form.formTarget = 'Keduanya';
  isModalOpen.value = true;
};

const openEditModal = (item: DropdownItem) => {
  editingId.value = item.id;
  form.category = item.category;
  form.label = item.label;
  form.formTarget = item.formTarget;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  form.reset();
};

const saveOption = () => {
  if (!form.label.trim()) {
    alert('Label opsi tidak boleh kosong!');
    return;
  }

  if (editingId.value) {
    // Mode Edit
    router.put(`/settings/dropdowns/${editingId.value}`, form.data(), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  } else {
    // Mode Tambah Baru
    router.post('/settings/dropdowns', form.data(), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
    });
  }
};

const deleteOption = (item: DropdownItem) => {
  if (confirm(`Apakah Anda yakin ingin menghapus opsi "${item.label}"?`)) {
    router.delete(`/settings/dropdowns/${item.id}`, {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head title="Pengaturan Master Data Dropdown" />

  <AuthenticatedLayout userRole="karyawan" v-model:active-menu="activeMenu">
    <div class="p-8 w-full mx-auto space-y-6 bg-[#F4F6F9] min-h-screen">
      
      <!-- PAGE HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200">
        <div>
          <div class="flex items-center gap-2 text-slate-800">
            <Sliders class="w-6 h-6 text-emerald-600" />
            <h1 class="text-2xl font-extrabold tracking-tight">Pengaturan Master Data Dropdown</h1>
          </div>
          <p class="text-xs text-slate-500 mt-1">
            Kelola opsi pilihan dropdown secara dinamis untuk memudahkan pengisian Form 3A & 3C.
          </p>
        </div>
      </div>

      <!-- MAIN CONTAINER: SIDEBAR MENU + CONTENT AREA -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- SIDEBAR NAVIGASI KATEGORI (3 COLS) -->
        <div class="lg:col-span-4 bg-white rounded-2xl p-3 border border-slate-200 shadow-xs space-y-1">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-3 py-2">
            PILIH KATEGORI DROPDOWN
          </p>
          
          <button
            v-for="cat in categories"
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

        <!-- MAIN CONTENT AREA (8 COLS) -->
        <div class="lg:col-span-8 space-y-5">
          
          <!-- CATEGORY INFO & ACTION BAR -->
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

            <!-- SEARCH BAR -->
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

          <!-- TABEL LIST OPSI DROPDOWN -->
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
                    v-for="(item, index) in filteredOptions" 
                    :key="item.id"
                    class="hover:bg-slate-50 transition-colors"
                  >
                    <td class="p-4 text-center font-bold text-slate-400">{{ index + 1 }}</td>
                    <td class="p-4 font-bold text-slate-900">{{ item.label }}</td>
                    <td class="p-4 text-center">
                      <span 
                        :class="[
                          'px-2.5 py-1 text-[10px] font-bold rounded-full border uppercase',
                          item.formTarget === '3A' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                          item.formTarget === '3C' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                          'bg-emerald-50 text-emerald-700 border-emerald-200'
                        ]"
                      >
                        Form {{ item.formTarget }}
                      </span>
                    </td>
                    <td class="p-4 text-center">
                      <div class="flex items-center justify-center gap-1">
                        <button
                          type="button"
                          @click="openEditModal(item)"
                          class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-200 rounded-lg transition-colors cursor-pointer"
                          title="Edit Opsi"
                        >
                          <Edit3 class="w-4 h-4" />
                        </button>
                        <button
                          type="button"
                          @click="deleteOption(item)"
                          class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                          title="Hapus Opsi"
                        >
                          <Trash2 class="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- EMPTY STATE -->
              <div v-if="filteredOptions.length === 0" class="p-12 text-center text-slate-400">
                <Database class="w-10 h-10 mx-auto stroke-1 text-slate-300 mb-2" />
                <p class="text-xs font-bold text-slate-600">Tidak ada opsi ditemukan.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Silakan klik "Tambah Opsi Baru" untuk membuat master data opsi baru.</p>
              </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 text-xs text-slate-500 flex justify-between items-center">
              <span>Total {{ filteredOptions.length }} Opsi</span>
              <span class="font-semibold text-slate-700">{{ activeCategoryMeta.label }}</span>
            </div>
          </div>

        </div>

      </div>

      <!-- MODAL FORM TAMBAH / EDIT OPSI -->
      <div 
        v-if="isModalOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4"
      >
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in-95 duration-200">
          
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-extrabold text-slate-900">
              {{ editingId ? 'Edit Opsi Dropdown' : 'Tambah Opsi Dropdown Baru' }}
            </h3>
            <button 
              type="button" 
              @click="closeModal" 
              class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100"
            >
              <X class="w-5 h-5" />
            </button>
          </div>

          <form @submit.prevent="saveOption" class="space-y-4">
            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                Kategori Dropdown
              </label>
              <input 
                type="text" 
                :value="activeCategoryMeta.label" 
                readonly 
                class="w-full px-3.5 py-2.5 text-xs font-bold bg-slate-100 border border-slate-200 rounded-xl text-slate-600 cursor-not-allowed"
              />
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                Label Opsi <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.label" 
                type="text" 
                required 
                placeholder="Masukkan teks opsi..." 
                class="w-full px-3.5 py-2.5 text-xs bg-[#F4F6F8] border border-transparent rounded-xl text-slate-900 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] font-semibold"
              />
            </div>

            <div>
              <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                Gunakan Di Form Laporan
              </label>
              <select 
                v-model="form.formTarget"
                class="w-full px-3.5 py-2.5 text-xs font-semibold bg-[#F4F6F8] border border-transparent rounded-xl text-slate-900 outline-none focus:bg-white focus:border-slate-300"
              >
                <option value="Keduanya">Form 3A & Form 3C (Keduanya)</option>
                <option value="3A">Khusus Form 3A</option>
                <option value="3C">Khusus Form 3C</option>
              </select>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
              >
                Batal
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-5 py-2 text-xs font-bold text-white bg-[#0E1B2E] hover:bg-slate-800 rounded-xl shadow-xs transition-colors disabled:opacity-50"
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