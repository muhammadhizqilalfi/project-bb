<template>
    <Head title="Pengaturan Akun" />

    <!-- Toast Notification -->
    <Transition 
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="transform translate-y-[-20px] opacity-0" 
        enter-to-class="transform translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in" 
        leave-from-class="transform translate-y-0 opacity-100"
        leave-to-class="transform translate-y-[-20px] opacity-0"
    >
        <div v-if="toastMessage"
            class="fixed top-5 right-5 z-50 bg-[#0E1B2E] text-white px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 border border-slate-700/50">
            <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                <Check class="w-4 h-4" />
            </div>
            <div>
                <p class="text-xs font-bold text-slate-200 uppercase tracking-wider">Notifikasi</p>
                <p class="text-sm font-medium text-white">{{ toastMessage }}</p>
            </div>
        </div>
    </Transition>

    <!-- Sub-Tabs Header Navigation Bar -->
    <div class="bg-white border-b border-slate-200/80 px-8 pt-4 flex items-center gap-8 shadow-xs">
        <!-- Tab 1: SEMUA KARYAWAN -->
        <button type="button" @click="switchTab('SEMUA KARYAWAN')" :class="[
            'pb-3.5 text-xs font-bold tracking-wider uppercase transition-all relative cursor-pointer',
            activeTab === 'SEMUA KARYAWAN'
                ? 'text-slate-900 font-extrabold'
                : 'text-slate-500 hover:text-slate-700'
        ]">
            SEMUA KARYAWAN
            <div v-if="activeTab === 'SEMUA KARYAWAN'"
                class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#FFD000] rounded-t-full"></div>
        </button>

        <!-- Tab 2: + TAMBAH KARYAWAN BARU -->
        <button type="button" @click="switchTab('+ TAMBAH KARYAWAN BARU')" :class="[
            'pb-3.5 text-xs font-bold tracking-wider uppercase transition-all relative cursor-pointer',
            activeTab === '+ TAMBAH KARYAWAN BARU'
                ? 'text-slate-900 font-extrabold'
                : 'text-slate-500 hover:text-slate-700'
        ]">
            + TAMBAH KARYAWAN BARU
            <div v-if="activeTab === '+ TAMBAH KARYAWAN BARU'"
                class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#FFD000] rounded-t-full"></div>
        </button>

        <!-- Tab 3: EDIT KARYAWAN (HANYA MUNCUL JIKA DITEKAN TOMBOL EDIT) -->
        <button v-if="editingEmployee" type="button" @click="activeTab = 'EDIT KARYAWAN'" :class="[
            'pb-3.5 text-xs font-bold tracking-wider uppercase transition-all relative cursor-pointer flex items-center gap-2 text-amber-600',
            activeTab === 'EDIT KARYAWAN'
                ? 'text-slate-900 font-extrabold'
                : 'hover:text-amber-700'
        ]">
            <Pencil class="w-3.5 h-3.5 text-amber-500" />
            EDIT: {{ editingEmployee.name }}
            <div v-if="activeTab === 'EDIT KARYAWAN'"
                class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#FFD000] rounded-t-full"></div>
        </button>
    </div>

    <!-- Main Tab Content Body -->
    <main class="flex-1 p-8 overflow-y-auto">
        <!-- TAB 1: SEMUA KARYAWAN -->
        <div v-if="activeTab === 'SEMUA KARYAWAN'" class="space-y-6">
            <!-- Stat Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: TOTAL AKUN STAF -->
                <div class="bg-white rounded-xl p-5 border border-slate-200/70 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            TOTAL AKUN STAF
                        </p>
                        <p class="text-2xl font-extrabold text-slate-900">
                            {{ stats.totalStaff || 0 }}
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-slate-100/90 text-slate-700 flex items-center justify-center shrink-0">
                        <Users class="w-5 h-5" />
                    </div>
                </div>

                <!-- Card 2: AKUN ACTIVE -->
                <div class="bg-white rounded-xl p-5 border border-slate-200/70 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                            AKUN ACTIVE
                        </p>
                        <p class="text-2xl font-extrabold text-emerald-600">
                            {{ stats.activeStaff || 0 }}
                        </p>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <UserCheck class="w-5 h-5" />
                    </div>
                </div>
            </div>

            <!-- Employee List Table Card -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200/70 overflow-hidden">
                <!-- Search Bar -->
                <div class="p-4 border-b border-slate-100">
                    <div class="relative max-w-md w-full">
                        <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input v-model="searchQuery" type="text"
                            placeholder="Cari berdasarkan nama, atau NIP"
                            class="w-full bg-[#F1F3F5] text-xs py-2.5 pl-10 pr-4 rounded-lg outline-none text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#FFD000] focus:bg-white transition-all" />
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#ECEFF1] text-slate-700 text-[11px] font-bold tracking-wider uppercase border-b border-slate-200/80">
                                <th class="py-3.5 px-6">KARYAWAN</th>
                                <th class="py-3.5 px-6">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr v-for="employee in filteredEmployees" :key="employee.id"
                                class="hover:bg-slate-50/80 transition-colors">
                                <!-- Karyawan -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-9 h-9 rounded-full bg-[#E6F4EA] text-[#0F5132] font-semibold text-xs flex items-center justify-center shrink-0">
                                            {{ getInitials(employee.name) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm leading-tight">
                                                {{ employee.name }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-normal mt-0.5">
                                                NIP. {{ employee.nip }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-6 font-medium">
                                    <button @click="startEdit(employee)" class="text-blue-600 hover:text-blue-800 transition-colors font-semibold mr-3 cursor-pointer">
                                        Edit
                                    </button>
                                    <button @click="deleteEmployee(employee)" class="text-red-500 hover:text-red-700 transition-colors font-semibold cursor-pointer">
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <!-- Empty Search Result State -->
                            <tr v-if="filteredEmployees.length === 0">
                                <td colspan="2" class="py-12 text-center text-slate-400">
                                    Tidak ada data karyawan yang cocok dengan pencarian "{{ searchQuery }}"
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: + TAMBAH KARYAWAN BARU -->
        <div v-else-if="activeTab === '+ TAMBAH KARYAWAN BARU'">
            <div class="bg-white rounded-xl shadow-xs border border-slate-200/70 p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-1">
                        Form Input Data & Akses Karyawan Baru
                    </h2>
                    <p class="text-sm text-slate-500">
                        Lengkapi data identitas pegawai dan tentukan kata sandi akun.
                    </p>
                    <div class="border-b border-slate-100 mt-6"></div>
                </div>

                <form @submit.prevent="saveEmployee">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                        <!-- LEFT COLUMN -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <UserPlus class="w-4 h-4" />
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm tracking-wide uppercase">
                                    DATA PENGGUNA
                                </h3>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2 block">
                                    NAMA LENGKAP
                                </label>
                                <input v-model="createForm.name" type="text" required
                                    placeholder="cth. Andi Hermawan, S.H."
                                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                                <p v-if="createForm.errors.name" class="text-red-500 text-xs mt-1">{{ createForm.errors.name }}</p>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2 block">
                                    NIP / IDENTITAS PEGAWAI
                                </label>
                                <input v-model="createForm.nip" type="text" required
                                    placeholder="19850122 201001 1 004"
                                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                                <p v-if="createForm.errors.nip" class="text-red-500 text-xs mt-1">{{ createForm.errors.nip }}</p>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <Key class="w-4 h-4" />
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm tracking-wide uppercase">
                                    PENGATURAN AKUN
                                </h3>
                            </div>
                            
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                        KATA SANDI SEMENTARA
                                    </label>
                                    <button type="button" @click="generatePassword('create')"
                                        class="text-xs font-semibold text-slate-800 hover:text-slate-950 underline cursor-pointer transition-colors">
                                        Generate Password
                                    </button>
                                </div>
                                <div class="relative">
                                    <input v-model="createForm.password" :type="showPassword ? 'text' : 'password'"
                                        required placeholder="••••••••••••"
                                        class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 outline-none pr-11 focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-1 cursor-pointer">
                                        <EyeOff v-if="showPassword" class="w-4 h-4" />
                                        <Eye v-else class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="createForm.errors.password" class="text-red-500 text-xs mt-1">{{ createForm.errors.password }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-8">
                        <button type="button" @click="activeTab = 'SEMUA KARYAWAN'"
                            class="bg-[#E2E8F0] hover:bg-[#CBD5E1] text-slate-800 px-6 py-2.5 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="createForm.processing"
                            class="bg-[#0E1B2E] hover:bg-[#1A2C42] text-white px-6 py-2.5 rounded-lg text-xs font-bold transition-colors cursor-pointer shadow-sm flex items-center gap-2 disabled:opacity-50">
                            Simpan & Buat Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 3: EDIT KARYAWAN -->
        <div v-else-if="activeTab === 'EDIT KARYAWAN' && editingEmployee">
            <div class="bg-white rounded-xl shadow-xs p-8">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-slate-900 mb-1 flex items-center gap-2">
                        Edit Data Karyawan: {{ editingEmployee.name }}
                    </h2>
                    <p class="text-sm text-slate-500">
                        Perbarui informasi data diri atau ubah kata sandi akun karyawan ini.
                    </p>
                    <div class="border-b border-slate-100 mt-6"></div>
                </div>

                <form @submit.prevent="updateEmployee">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                        <!-- LEFT COLUMN -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <Pencil class="w-4 h-4" />
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm tracking-wide uppercase">
                                    DATA PENGGUNA
                                </h3>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2 block">
                                    NAMA LENGKAP
                                </label>
                                <input v-model="editForm.name" type="text" required
                                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                                <p v-if="editForm.errors.name" class="text-red-500 text-xs mt-1">{{ editForm.errors.name }}</p>
                            </div>

                            <div>
                                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2 block">
                                    NIP / IDENTITAS PEGAWAI
                                </label>
                                <input v-model="editForm.nip" type="text" required
                                    class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                                <p v-if="editForm.errors.nip" class="text-red-500 text-xs mt-1">{{ editForm.errors.nip }}</p>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                    <Key class="w-4 h-4" />
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm tracking-wide uppercase">
                                    UBAH KATA SANDI (OPSIONAL)
                                </h3>
                            </div>
                            
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                        KATA SANDI BARU
                                    </label>
                                    <button type="button" @click="generatePassword('edit')"
                                        class="text-xs font-semibold text-slate-800 hover:text-slate-950 underline cursor-pointer transition-colors">
                                        Generate Password
                                    </button>
                                </div>
                                <div class="relative">
                                    <input v-model="editForm.password" :type="showPassword ? 'text' : 'password'"
                                        placeholder="Kosongkan jika tidak ingin diubah"
                                        class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 outline-none pr-11 focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all" />
                                    <button type="button" @click="showPassword = !showPassword"
                                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-1 cursor-pointer">
                                        <EyeOff v-if="showPassword" class="w-4 h-4" />
                                        <Eye v-else class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="editForm.errors.password" class="text-red-500 text-xs mt-1">{{ editForm.errors.password }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-8">
                        <button type="button" @click="cancelEdit"
                            class="bg-[#E2E8F0] hover:bg-[#CBD5E1] text-slate-800 px-6 py-2.5 rounded-lg text-xs font-bold transition-colors cursor-pointer">
                            Batal Edit
                        </button>
                        <button type="submit" :disabled="editForm.processing"
                            class="bg-black hover:bg-amber-700 text-white px-6 py-2.5 rounded-lg text-xs font-bold transition-colors cursor-pointer shadow-sm flex items-center gap-2 disabled:opacity-50">
                            Update Data Karyawan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>

<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import SidebarLayout from '@/Layouts/Layout.vue';
import {
    Users,
    UserCheck,
    Search,
    UserPlus,
    Pencil,
    Key,
    Eye,
    EyeOff,
    Check
} from 'lucide-vue-next';

// 1. Dapatkan data riil dari Database Laravel melalui Props
const props = defineProps({
    employees: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({ totalStaff: 0, activeStaff: 0 })
    }
});

defineOptions({
    layout: SidebarLayout,
});

// Component State
const activeTab = ref('SEMUA KARYAWAN');
const searchQuery = ref('');
const showPassword = ref(false);
const toastMessage = ref('');
const editingEmployee = ref(null); // Menyimpan objek karyawan yang sedang di-edit

// Form untuk Tambah Data Baru
const createForm = useForm({
    name: '',
    nip: '',
    password: ''
});

// Form untuk Edit Data
const editForm = useForm({
    id: null,
    name: '',
    nip: '',
    password: ''
});

// Computed Search Filter dari Props Employees
const filteredEmployees = computed(() => {
    if (!searchQuery.value.trim()) return props.employees;
    const query = searchQuery.value.toLowerCase();
    return props.employees.filter(emp =>
        emp.name?.toLowerCase().includes(query) ||
        emp.nip?.toLowerCase().includes(query)
    );
});

// Helper Initials
const getInitials = (name) => {
    if (!name) return 'PE';
    const parts = name.trim().split(' ').filter(Boolean);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Switch Tab Handler
const switchTab = (tab) => {
    activeTab.value = tab;
    if (tab !== 'EDIT KARYAWAN') {
        editingEmployee.value = null; // Sembunyikan tab edit jika pengguna berpindah ke tab lain
    }
};

// Start Edit Handler (Memicu kemunculan Tab Edit)
const startEdit = (employee) => {
    editingEmployee.value = employee;
    editForm.id = employee.id;
    editForm.name = employee.name;
    editForm.nip = employee.nip;
    editForm.password = ''; // Kosongkan password saat edit
    activeTab.value = 'EDIT KARYAWAN'; // Otomatis berpindah ke tab edit
};

const cancelEdit = () => {
    editingEmployee.value = null;
    editForm.reset();
    activeTab.value = 'SEMUA KARYAWAN';
};

// Random Password Generator
const generatePassword = (targetForm = 'create') => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*';
    let pass = '';
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    if (targetForm === 'create') {
        createForm.password = pass;
    } else {
        editForm.password = pass;
    }
    showPassword.value = true;
    showToast('Password baru berhasil dibuat!');
};

// Toast
const showToast = (msg) => {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 3500);
};

// Save New Employee
const saveEmployee = () => {
    createForm.post('/employees', {
        onSuccess: () => {
            createForm.reset();
            showToast('Karyawan baru berhasil ditambahkan!');
            activeTab.value = 'SEMUA KARYAWAN';
        }
    });
};

// Update Employee
const updateEmployee = () => {
    editForm.put(`/employees/${editForm.id}`, {
        onSuccess: () => {
            showToast(`Data ${editForm.name} berhasil diperbarui!`);
            cancelEdit();
        }
    });
};

// Delete Employee
const deleteEmployee = (employee) => {
    if (confirm(`Apakah Anda yakin ingin menghapus akun karyawan "${employee.name}"?`)) {
        router.delete(`/employees/${employee.id}`, {
            onSuccess: () => {
                showToast(`Karyawan ${employee.name} berhasil dihapus!`);
            }
        });
    }
};
</script>