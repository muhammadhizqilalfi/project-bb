<template>
    <Head title="Beranda - Admin" />
    <AuthenticatedLayout 
        userRole="admin" 
        v-model:active-menu="activeMenu"
        :userName="page.props.auth?.user?.name ?? '-'"
        :nip="`NIP. ${page.props.auth?.user?.nip ?? '-'}`"
    >
        <!-- Toast Notification -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-y-[-20px] opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform translate-y-[-20px] opacity-0"
        >
            <div
                v-if="toastMessage"
                class="fixed top-5 right-5 z-50 bg-[#0E1B2E] text-white px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 border border-slate-700/50"
            >
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
            <button
                type="button"
                @click="activeTab = 'SEMUA KARYAWAN'"
                :class="[
                    'pb-3.5 text-xs font-bold tracking-wider uppercase transition-all relative cursor-pointer',
                    activeTab === 'SEMUA KARYAWAN'
                        ? 'text-slate-900 font-extrabold'
                        : 'text-slate-500 hover:text-slate-700'
                ]"
            >
                SEMUA KARYAWAN
                <!-- Active Indicator Yellow Line -->
                <div
                    v-if="activeTab === 'SEMUA KARYAWAN'"
                    class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#FFD000] rounded-t-full"
                ></div>
            </button>

            <!-- Tab 2: + TAMBAH KARYAWAN BARU -->
            <button
                type="button"
                @click="activeTab = '+ TAMBAH KARYAWAN BARU'"
                :class="[
                    'pb-3.5 text-xs font-bold tracking-wider uppercase transition-all relative cursor-pointer',
                    activeTab === '+ TAMBAH KARYAWAN BARU'
                        ? 'text-slate-900 font-extrabold'
                        : 'text-slate-500 hover:text-slate-700'
                ]"
            >
                + TAMBAH KARYAWAN BARU
                <!-- Active Indicator Yellow Line -->
                <div
                    v-if="activeTab === '+ TAMBAH KARYAWAN BARU'"
                    class="absolute bottom-0 left-0 right-0 h-[3px] bg-[#FFD000] rounded-t-full"
                ></div>
            </button>
        </div>

        <!-- Main Tab Content Body -->
        <main class="flex-1 p-8 overflow-y-auto">
            <!-- TAB 1: SEMUA KARYAWAN -->
            <div v-if="activeTab === 'SEMUA KARYAWAN'" class="space-y-6">
                <!-- Stat Cards Row (3 Cards) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card 1: TOTAL AKUN STAF -->
                    <div class="bg-white rounded-xl p-5 border border-slate-200/70 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                TOTAL AKUN STAF
                            </p>
                            <p class="text-2xl font-extrabold text-slate-900">
                                {{ stats.totalStaff }}
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
                                {{ stats.activeStaff }}
                            </p>
                        </div>
                        <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <UserCheck class="w-5 h-5" />
                        </div>
                    </div>

                    <!-- Card 3: ADMINISTRATOR -->
                    <div class="bg-white rounded-xl p-5 border border-slate-200/70 shadow-xs flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                                ADMINISTRATOR
                            </p>
                            <p class="text-2xl font-extrabold text-slate-900">
                                {{ stats.administrators }}
                            </p>
                        </div>
                        <div class="w-11 h-11 rounded-lg bg-sky-50 text-sky-700 flex items-center justify-center shrink-0">
                            <Shield class="w-5 h-5" />
                        </div>
                    </div>
                </div>

                <!-- Employee List Table Card -->
                <div class="bg-white rounded-xl shadow-xs border border-slate-200/70 overflow-hidden">
                    <!-- Search Bar -->
                    <div class="p-4 border-b border-slate-100">
                        <div class="relative max-w-md w-full">
                            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari berdasarkan nama, NIP, atau jabatan..."
                                class="w-full bg-[#F1F3F5] text-xs py-2.5 pl-10 pr-4 rounded-lg outline-none text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-[#FFD000] focus:bg-white transition-all"
                            />
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-[#ECEFF1] text-slate-700 text-[11px] font-bold tracking-wider uppercase border-b border-slate-200/80">
                                    <th class="py-3.5 px-6">KARYAWAN</th>
                                    <th class="py-3.5 px-6">JABATAN</th>
                                    <th class="py-3.5 px-6">ROLE</th>
                                    <th class="py-3.5 px-6">TERAKHIR AKTIF</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <tr
                                    v-for="employee in filteredEmployees"
                                    :key="employee.id"
                                    class="hover:bg-slate-50/80 transition-colors"
                                >
                                    <!-- Karyawan (Avatar + Name + NIP) -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-9 h-9 rounded-full bg-[#E6F4EA] text-[#0F5132] font-semibold text-xs flex items-center justify-center shrink-0">
                                                {{ employee.initials }}
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

                                    <!-- Jabatan -->
                                    <td class="py-4 px-6 font-medium text-slate-700">
                                        {{ employee.jabatan }}
                                    </td>

                                    <!-- Role -->
                                    <td class="py-4 px-6">
                                        <span class="inline-block bg-slate-100 text-slate-700 font-bold text-[10px] px-2.5 py-1 rounded tracking-wider uppercase">
                                            {{ employee.role }}
                                        </span>
                                    </td>

                                    <!-- Terakhir Aktif -->
                                    <td class="py-4 px-6 text-slate-600 font-normal">
                                        {{ employee.lastActive }}
                                    </td>
                                </tr>

                                <!-- Empty Search Result State -->
                                <tr v-if="filteredEmployees.length === 0">
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        Tidak ada data karyawan yang cocok dengan pencarian "{{ searchQuery }}"
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer / Pagination -->
                    <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                        <div>
                            Menampilkan 1-{{ filteredEmployees.length }} dari 1,248 data
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                @click="currentPage = Math.max(1, currentPage - 1)"
                                class="w-7 h-7 rounded flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                            >
                                <ChevronLeft class="w-4 h-4" />
                            </button>
                            <button
                                v-for="page in [1, 2, 3]"
                                :key="page"
                                type="button"
                                @click="currentPage = page"
                                :class="[
                                    'w-7 h-7 rounded text-xs font-bold transition-colors cursor-pointer flex items-center justify-center',
                                    currentPage === page
                                        ? 'bg-slate-900 text-white'
                                        : 'text-slate-600 hover:bg-slate-100'
                                ]"
                            >
                                {{ page }}
                            </button>
                            <span class="px-1 text-slate-400">...</span>
                            <button
                                type="button"
                                @click="currentPage = 125"
                                :class="[
                                    'w-7 h-7 rounded text-xs font-bold transition-colors cursor-pointer flex items-center justify-center',
                                    currentPage === 125
                                        ? 'bg-slate-900 text-white'
                                        : 'text-slate-600 hover:bg-slate-100'
                                ]"
                            >
                                125
                            </button>
                            <button
                                type="button"
                                @click="currentPage = Math.min(125, currentPage + 1)"
                                class="w-7 h-7 rounded flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                            >
                                <ChevronRight class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: + TAMBAH KARYAWAN BARU -->
            <div v-else-if="activeTab === '+ TAMBAH KARYAWAN BARU'">
                <div class="bg-white rounded-xl shadow-xs border border-slate-200/70 p-8">
                    <!-- Form Header -->
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-slate-900 mb-1">
                            Form Input Data & Akses Karyawan Baru
                        </h2>
                        <p class="text-sm text-slate-500">
                            Lengkapi data identitas pegawai dan tentukan hak akses sistem secara detail.
                        </p>
                        <div class="border-b border-slate-100 mt-6"></div>
                    </div>

                    <!-- Form Main Content Grid -->
                    <form @submit.prevent="saveEmployee">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                            <!-- LEFT COLUMN: DATA DIRI & JABATAN -->
                            <div class="space-y-6">
                                <!-- Section Header -->
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                        <UserPlus class="w-4 h-4" />
                                    </div>
                                    <h3 class="font-bold text-slate-900 text-sm tracking-wide uppercase">
                                        DATA DIRI & JABATAN
                                    </h3>
                                </div>

                                <!-- Field 1: NAMA LENGKAP -->
                                <div>
                                    <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2 block">
                                        NAMA LENGKAP
                                    </label>
                                    <input
                                        v-model="newEmployee.name"
                                        type="text"
                                        required
                                        placeholder="cth. Andi Hermawan, S.H."
                                        class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                                    />
                                </div>

                                <!-- Field 2: NIP / IDENTITAS PEGAWAI -->
                                <div>
                                    <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2 block">
                                        NIP / IDENTITAS PEGAWAI
                                    </label>
                                    <input
                                        v-model="newEmployee.nip"
                                        type="text"
                                        required
                                        placeholder="19850122 201001 1 004"
                                        class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 placeholder-slate-400 outline-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                                    />
                                </div>
                            </div>

                            <!-- RIGHT COLUMN: PENGATURAN AKUN & AKSES -->
                            <div class="space-y-6">
                                <!-- Section Header -->
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                        <Key class="w-4 h-4" />
                                    </div>
                                    <h3 class="font-bold text-slate-900 text-sm tracking-wide uppercase">
                                        PENGATURAN AKUN & AKSES
                                    </h3>
                                </div>

                                <!-- Field 1: ROLE / HAK AKSES -->
                                <div>
                                    <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-2 block">
                                        ROLE / HAK AKSES
                                    </label>
                                    <div class="relative">
                                        <select
                                            v-model="newEmployee.role"
                                            required
                                            class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-700 outline-none appearance-none focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all cursor-pointer"
                                        >
                                            <option value="" disabled selected>Tentukan level akses...</option>
                                            <option value="ADMIN">ADMIN</option>
                                            <option value="STAF">STAF</option>
                                        </select>
                                        <ChevronDown class="w-4 h-4 text-slate-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none" />
                                    </div>
                                </div>

                                <!-- Field 2: KATA SANDI SEMENTARA -->
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                            KATA SANDI SEMENTARA
                                        </label>
                                        <button
                                            type="button"
                                            @click="handleGeneratePassword"
                                            class="text-xs font-semibold text-slate-800 hover:text-slate-950 underline cursor-pointer transition-colors"
                                        >
                                            Generate Password
                                        </button>
                                    </div>
                                    <div class="relative">
                                        <input
                                            v-model="newEmployee.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            required
                                            placeholder="••••••••••••"
                                            class="w-full bg-[#F4F6F8] border border-transparent rounded-lg px-4 py-3 text-xs text-slate-800 outline-none pr-11 focus:bg-white focus:border-slate-300 focus:ring-2 focus:ring-[#FFD000] transition-all"
                                        />
                                        <button
                                            type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors p-1 cursor-pointer"
                                        >
                                            <EyeOff v-if="showPassword" class="w-4 h-4" />
                                            <Eye v-else class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Notice Banner -->
                        <div class="bg-[#EBF3FA] text-[#1E517B] rounded-lg p-4 text-xs flex items-center gap-3.5 my-8 border border-[#D9E7F4]">
                            <div class="w-5 h-5 rounded-full border border-[#1E517B]/40 flex items-center justify-center shrink-0 font-semibold text-[11px]">
                                i
                            </div>
                            <p class="leading-relaxed">
                                Pegawai akan menerima email berisi tautan aktivasi dan informasi login sementara. Pastikan alamat email kantor yang dimasukkan sudah benar untuk menghindari hambatan sinkronisasi data internal.
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                type="button"
                                @click="resetForm"
                                class="bg-[#E2E8F0] hover:bg-[#CBD5E1] text-slate-800 px-6 py-2.5 rounded-lg text-xs font-bold transition-colors cursor-pointer"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="bg-[#0E1B2E] hover:bg-[#1A2C42] text-white px-6 py-2.5 rounded-lg text-xs font-bold transition-colors cursor-pointer shadow-sm flex items-center gap-2"
                            >
                                Simpan & Buat Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'; 
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Users,
    UserCheck,
    Shield,
    Search, 
    UserPlus,
    Key,
    Eye,
    EyeOff,
    ChevronDown,
    ChevronRight,
    ChevronLeft,
    Check
} from 'lucide-vue-next';

// State Management
const page = usePage();
const activeMenu = ref('BERANDA');
const activeTab = ref('SEMUA KARYAWAN');
const searchQuery = ref('');
const currentPage = ref(1);
const showPassword = ref(false);
const toastMessage = ref('');

// Reactive Stats
const stats = ref({
    totalStaff: 665,
    activeStaff: 661,
    administrators: 19,
});

// Employee List Data
const employees = ref([
    {
        id: 1,
        initials: 'AH',
        name: 'Andi Hermawan, S.H.',
        nip: '19850122 201001 1 004',
        jabatan: 'Kepala Seksi Intelijen',
        role: 'ADMIN',
        lastActive: '2 Menit yang lalu'
    },
    {
        id: 2,
        initials: 'SR',
        name: 'Siti Rahmawati',
        nip: '19920315 201503 2 001',
        jabatan: 'Staf Administrasi Barang Bukti',
        role: 'STAF',
        lastActive: '1 Jam yang lalu'
    },
    {
        id: 3,
        initials: 'BS',
        name: 'Budi Santoso, S.Kom',
        nip: '19881105 201202 1 003',
        jabatan: 'Analis Sistem Informasi',
        role: 'ADMIN',
        lastActive: 'Hari ini, 08:45'
    },
    {
        id: 4,
        initials: 'FA',
        name: 'Fauzan Azim',
        nip: '19950620 201801 1 009',
        jabatan: 'Pengelola Barang Bukti',
        role: 'STAF',
        lastActive: 'Kemarin, 16:20'
    }
]);

// New Employee Form State
const newEmployee = ref({
    name: '',
    nip: '',
    jabatan: 'Staf Intelijen & Barang Bukti',
    role: '',
    password: ''
});

// Filtered Employees Computed
const filteredEmployees = computed(() => {
    if (!searchQuery.value.trim()) return employees.value;
    const query = searchQuery.value.toLowerCase();
    return employees.value.filter(emp =>
        emp.name.toLowerCase().includes(query) ||
        emp.nip.toLowerCase().includes(query) ||
        emp.jabatan.toLowerCase().includes(query) ||
        emp.role.toLowerCase().includes(query)
    );
});

// Helper: Generate Initials
const getInitials = (name) => {
    if (!name) return 'PE';
    const parts = name.trim().split(' ').filter(Boolean);
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Password Generator
const handleGeneratePassword = () => {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*';
    let pass = '';
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    newEmployee.value.password = pass;
    showPassword.value = true;
    showToast('Password sementara telah dibuat!');
};

// Toast notification helper
const showToast = (msg) => {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 3500);
};

// Reset Form
const resetForm = () => {
    newEmployee.value = {
        name: '',
        nip: '',
        jabatan: 'Staf Intelijen & Barang Bukti',
        role: '',
        password: ''
    };
    showPassword.value = false;
    activeTab.value = 'SEMUA KARYAWAN';
};

// Form Submission Handler
const saveEmployee = () => {
    if (!newEmployee.value.name || !newEmployee.value.nip || !newEmployee.value.role) {
        return;
    }

    const created = {
        id: Date.now(),
        initials: getInitials(newEmployee.value.name),
        name: newEmployee.value.name,
        nip: newEmployee.value.nip,
        jabatan: newEmployee.value.jabatan || 'Staf Barang Bukti',
        role: newEmployee.value.role,
        lastActive: 'Baru saja'
    };

    employees.value.unshift(created);

    // Update Stats
    stats.value.totalStaff += 1;
    stats.value.activeStaff += 1;
    if (created.role === 'ADMIN') {
        stats.value.administrators += 1;
    }

    showToast(`Akun karyawan ${created.name} berhasil dibuat!`);
    resetForm();
    activeTab.value = 'SEMUA KARYAWAN';
};
</script>