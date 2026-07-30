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
        <div
            v-if="toastMessage"
            class="fixed top-5 right-5 z-50 flex items-center gap-3 rounded-xl border border-slate-700/50 bg-[#0E1B2E] px-5 py-3.5 text-white shadow-2xl"
        >
            <div
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400"
            >
                <Check class="h-4 w-4" />
            </div>
            <div>
                <p
                    class="text-xs font-bold tracking-wider text-slate-200 uppercase"
                >
                    Notifikasi
                </p>
                <p class="text-sm font-medium text-white">{{ toastMessage }}</p>
            </div>
        </div>
    </Transition>

    <!-- Sub-Tabs Header Navigation Bar -->
    <div
        class="flex items-center gap-8 border-b border-slate-200/80 bg-white px-8 pt-4 shadow-xs"
    >
        <!-- Tab 1: SEMUA KARYAWAN -->
        <button
            type="button"
            @click="switchTab('SEMUA KARYAWAN')"
            :class="[
                'relative cursor-pointer pb-3.5 text-xs font-bold tracking-wider uppercase transition-all',
                activeTab === 'SEMUA KARYAWAN'
                    ? 'font-extrabold text-slate-900'
                    : 'text-slate-500 hover:text-slate-700',
            ]"
        >
            SEMUA KARYAWAN
            <div
                v-if="activeTab === 'SEMUA KARYAWAN'"
                class="absolute right-0 bottom-0 left-0 h-[3px] rounded-t-full bg-[#FFD000]"
            ></div>
        </button>

        <!-- Tab 2: + TAMBAH KARYAWAN BARU -->
        <button
            type="button"
            @click="switchTab('+ TAMBAH KARYAWAN BARU')"
            :class="[
                'relative cursor-pointer pb-3.5 text-xs font-bold tracking-wider uppercase transition-all',
                activeTab === '+ TAMBAH KARYAWAN BARU'
                    ? 'font-extrabold text-slate-900'
                    : 'text-slate-500 hover:text-slate-700',
            ]"
        >
            + TAMBAH KARYAWAN BARU
            <div
                v-if="activeTab === '+ TAMBAH KARYAWAN BARU'"
                class="absolute right-0 bottom-0 left-0 h-[3px] rounded-t-full bg-[#FFD000]"
            ></div>
        </button>

        <!-- Tab 3: EDIT KARYAWAN (HANYA MUNCUL JIKA DITEKAN TOMBOL EDIT) -->
        <button
            v-if="editingEmployee"
            type="button"
            @click="activeTab = 'EDIT KARYAWAN'"
            :class="[
                'relative flex cursor-pointer items-center gap-2 pb-3.5 text-xs font-bold tracking-wider text-amber-600 uppercase transition-all',
                activeTab === 'EDIT KARYAWAN'
                    ? 'font-extrabold text-slate-900'
                    : 'hover:text-amber-700',
            ]"
        >
            <Pencil class="h-3.5 w-3.5 text-amber-500" />
            EDIT: {{ editingEmployee.name }}
            <div
                v-if="activeTab === 'EDIT KARYAWAN'"
                class="absolute right-0 bottom-0 left-0 h-[3px] rounded-t-full bg-[#FFD000]"
            ></div>
        </button>
    </div>

    <!-- Main Tab Content Body -->
    <main class="flex-1 overflow-y-auto p-8">
        <!-- TAB 1: SEMUA KARYAWAN -->
        <div v-if="activeTab === 'SEMUA KARYAWAN'" class="space-y-6">
            <!-- Stat Cards Row -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Card 1: TOTAL AKUN STAF -->
                <div
                    class="flex items-center justify-between rounded-xl border border-slate-200/70 bg-white p-5 shadow-xs"
                >
                    <div>
                        <p
                            class="mb-1 text-[11px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            TOTAL AKUN STAF
                        </p>
                        <p class="text-2xl font-extrabold text-slate-900">
                            {{ stats.totalStaff || 0 }}
                        </p>
                    </div>
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-100/90 text-slate-700"
                    >
                        <Users class="h-5 w-5" />
                    </div>
                </div>

                <!-- Card 2: AKUN ACTIVE -->
                <div
                    class="flex items-center justify-between rounded-xl border border-slate-200/70 bg-white p-5 shadow-xs"
                >
                    <div>
                        <p
                            class="mb-1 text-[11px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            AKUN ACTIVE
                        </p>
                        <p class="text-2xl font-extrabold text-emerald-600">
                            {{ stats.activeStaff || 0 }}
                        </p>
                    </div>
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                    >
                        <UserCheck class="h-5 w-5" />
                    </div>
                </div>
            </div>

            <!-- Employee List Table Card -->
            <div
                class="overflow-hidden rounded-xl border border-slate-200/70 bg-white shadow-xs"
            >
                <!-- Search Bar -->
                <div class="border-b border-slate-100 p-4">
                    <div
                        class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                    >
                        <div class="relative w-full max-w-md">
                            <Search
                                class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari berdasarkan nama, atau NIP"
                                class="w-full rounded-lg bg-[#F1F3F5] py-2.5 pr-4 pl-10 text-xs text-slate-800 placeholder-slate-400 transition-all outline-none focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="text-[11px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                Urutkan
                            </span>
                            <select
                                v-model="sortOrder"
                                @change="handleSortChange"
                                class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 transition-colors outline-none hover:border-slate-300 focus:border-[#FFD000] focus:ring-2 focus:ring-[#FFD000]"
                            >
                                <option value="latest">Terbaru</option>
                                <option value="oldest">Terlama</option>
                                <option value="az">A-Z</option>
                                <option value="za">Z-A</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto px-6 py-4">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-b border-slate-200/80 bg-[#ECEFF1] text-[11px] font-bold tracking-wider text-slate-700 uppercase"
                            >
                                <th class="px-8 py-3.5">KARYAWAN</th>
                                <th class="px-8 py-3.5">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr
                                v-for="employee in visibleEmployees"
                                :key="employee.id"
                                class="transition-colors hover:bg-slate-50/80"
                            >
                                <!-- Karyawan -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3.5">
                                        <div
                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#E6F4EA] text-xs font-semibold text-[#0F5132]"
                                        >
                                            {{ getInitials(employee.name) }}
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm leading-tight font-bold text-slate-900"
                                            >
                                                {{ employee.name }}
                                            </div>
                                            <div
                                                class="mt-0.5 text-[11px] font-normal text-slate-400"
                                            >
                                                NIP. {{ employee.nip }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 font-medium">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="startEdit(employee)"
                                            class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-blue-100 text-blue-600 transition-colors hover:bg-blue-50 hover:text-blue-800"
                                            aria-label="Edit karyawan"
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </button>
                                        <button
                                            type="button"
                                            @click="deleteEmployee(employee)"
                                            class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-red-100 text-red-500 transition-colors hover:bg-red-50 hover:text-red-700"
                                            aria-label="Hapus karyawan"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty Search Result State -->
                            <tr v-if="visibleEmployees.length === 0">
                                <td
                                    colspan="2"
                                    class="py-12 text-center text-slate-400"
                                >
                                    Tidak ada data karyawan yang cocok dengan
                                    pencarian "{{ searchQuery }}"
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer / Pagination -->
                <div
                    class="flex flex-col items-center justify-between gap-4 border-t border-slate-100 px-6 py-4 text-xs text-slate-500 sm:flex-row"
                >
                    <div>
                        <span v-if="paginationTotal === 0">
                            Tidak ada data karyawan.
                        </span>
                        <span v-else>
                            Menampilkan {{ paginationFrom }}-{{
                                paginationTo
                            }}
                            dari {{ paginationTotal }} data
                        </span>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-center gap-2 sm:justify-end"
                    >
                        <button
                            type="button"
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                            aria-label="Halaman sebelumnya"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>

                        <button
                            v-for="page in paginationPages"
                            :key="page.key"
                            type="button"
                            @click="
                                page.type === 'page' && goToPage(page.value)
                            "
                            :disabled="page.type === 'ellipsis'"
                            :class="[
                                'flex h-8 min-w-8 items-center justify-center rounded px-2 text-xs font-bold transition-colors',
                                page.type === 'ellipsis'
                                    ? 'cursor-default text-slate-400'
                                    : currentPage === page.value
                                      ? 'bg-slate-900 text-white'
                                      : 'cursor-pointer text-slate-600 hover:bg-slate-100',
                            ]"
                        >
                            <span v-if="page.type === 'page'">{{
                                page.value
                            }}</span>
                            <span v-else>...</span>
                        </button>

                        <button
                            type="button"
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === lastPage"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                            aria-label="Halaman berikutnya"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: + TAMBAH KARYAWAN BARU -->
        <div v-else-if="activeTab === '+ TAMBAH KARYAWAN BARU'">
            <div
                class="rounded-xl border border-slate-200/70 bg-white p-8 shadow-xs"
            >
                <div class="mb-8">
                    <h2 class="mb-1 text-xl font-bold text-slate-900">
                        Form Input Data & Akses Karyawan Baru
                    </h2>
                    <p class="text-sm text-slate-500">
                        Lengkapi data identitas pegawai dan tentukan kata sandi
                        akun.
                    </p>
                    <div class="mt-6 border-b border-slate-100"></div>
                </div>

                <form @submit.prevent="saveEmployee">
                    <div
                        class="grid grid-cols-1 gap-x-12 gap-y-8 md:grid-cols-2"
                    >
                        <!-- LEFT COLUMN -->
                        <div class="space-y-6">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-700"
                                >
                                    <UserPlus class="h-4 w-4" />
                                </div>
                                <h3
                                    class="text-sm font-bold tracking-wide text-slate-900 uppercase"
                                >
                                    DATA PENGGUNA
                                </h3>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >
                                    NAMA LENGKAP
                                </label>
                                <input
                                    v-model="createForm.name"
                                    type="text"
                                    required
                                    placeholder="cth. Andi Hermawan, S.H."
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 text-xs text-slate-800 placeholder-slate-400 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                                <p
                                    v-if="createForm.errors.name"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ createForm.errors.name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >
                                    NIP / IDENTITAS PEGAWAI
                                </label>
                                <input
                                    v-model="createForm.nip"
                                    type="text"
                                    required
                                    placeholder="19850122 201001 1 004"
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 text-xs text-slate-800 placeholder-slate-400 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                                <p
                                    v-if="createForm.errors.nip"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ createForm.errors.nip }}
                                </p>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="space-y-6">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-700"
                                >
                                    <Key class="h-4 w-4" />
                                </div>
                                <h3
                                    class="text-sm font-bold tracking-wide text-slate-900 uppercase"
                                >
                                    PENGATURAN AKUN
                                </h3>
                            </div>

                            <div>
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <label
                                        class="text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        KATA SANDI SEMENTARA
                                    </label>
                                    <button
                                        type="button"
                                        @click="generatePassword('create')"
                                        class="cursor-pointer text-xs font-semibold text-slate-800 underline transition-colors hover:text-slate-950"
                                    >
                                        Generate Password
                                    </button>
                                </div>
                                <div class="relative">
                                    <input
                                        v-model="createForm.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        required
                                        placeholder="••••••••••••"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 pr-11 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute top-1/2 right-3.5 -translate-y-1/2 cursor-pointer p-1 text-slate-400 transition-colors hover:text-slate-600"
                                    >
                                        <EyeOff
                                            v-if="showPassword"
                                            class="h-4 w-4"
                                        />
                                        <Eye v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <p
                                    v-if="createForm.errors.password"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ createForm.errors.password }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-8">
                        <button
                            type="button"
                            @click="activeTab = 'SEMUA KARYAWAN'"
                            class="cursor-pointer rounded-lg bg-[#E2E8F0] px-6 py-2.5 text-xs font-bold text-slate-800 transition-colors hover:bg-[#CBD5E1]"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="flex cursor-pointer items-center gap-2 rounded-lg bg-[#0E1B2E] px-6 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-[#1A2C42] disabled:opacity-50"
                        >
                            Simpan & Buat Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 3: EDIT KARYAWAN -->
        <div v-else-if="activeTab === 'EDIT KARYAWAN' && editingEmployee">
            <div class="rounded-xl bg-white p-8 shadow-xs">
                <div class="mb-8">
                    <h2
                        class="mb-1 flex items-center gap-2 text-xl font-bold text-slate-900"
                    >
                        Edit Data Karyawan: {{ editingEmployee.name }}
                    </h2>
                    <p class="text-sm text-slate-500">
                        Perbarui informasi data diri atau ubah kata sandi akun
                        karyawan ini.
                    </p>
                    <div class="mt-6 border-b border-slate-100"></div>
                </div>

                <form @submit.prevent="updateEmployee">
                    <div
                        class="grid grid-cols-1 gap-x-12 gap-y-8 md:grid-cols-2"
                    >
                        <!-- LEFT COLUMN -->
                        <div class="space-y-6">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-700"
                                >
                                    <Pencil class="h-4 w-4" />
                                </div>
                                <h3
                                    class="text-sm font-bold tracking-wide text-slate-900 uppercase"
                                >
                                    DATA PENGGUNA
                                </h3>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >
                                    NAMA LENGKAP
                                </label>
                                <input
                                    v-model="editForm.name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                                <p
                                    v-if="editForm.errors.name"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ editForm.errors.name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                >
                                    NIP / IDENTITAS PEGAWAI
                                </label>
                                <input
                                    v-model="editForm.nip"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                />
                                <p
                                    v-if="editForm.errors.nip"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ editForm.errors.nip }}
                                </p>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="space-y-6">
                            <div class="mb-6 flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-700"
                                >
                                    <Key class="h-4 w-4" />
                                </div>
                                <h3
                                    class="text-sm font-bold tracking-wide text-slate-900 uppercase"
                                >
                                    UBAH KATA SANDI (OPSIONAL)
                                </h3>
                            </div>

                            <div>
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <label
                                        class="text-[11px] font-bold tracking-wider text-slate-600 uppercase"
                                    >
                                        KATA SANDI BARU
                                    </label>
                                    <button
                                        type="button"
                                        @click="generatePassword('edit')"
                                        class="cursor-pointer text-xs font-semibold text-slate-800 underline transition-colors hover:text-slate-950"
                                    >
                                        Generate Password
                                    </button>
                                </div>
                                <div class="relative">
                                    <input
                                        v-model="editForm.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        placeholder="Kosongkan jika tidak ingin diubah"
                                        class="w-full rounded-lg border border-transparent bg-[#F4F6F8] px-4 py-3 pr-11 text-xs text-slate-800 transition-all outline-none focus:border-slate-300 focus:bg-white focus:ring-2 focus:ring-[#FFD000]"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute top-1/2 right-3.5 -translate-y-1/2 cursor-pointer p-1 text-slate-400 transition-colors hover:text-slate-600"
                                    >
                                        <EyeOff
                                            v-if="showPassword"
                                            class="h-4 w-4"
                                        />
                                        <Eye v-else class="h-4 w-4" />
                                    </button>
                                </div>
                                <p
                                    v-if="editForm.errors.password"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ editForm.errors.password }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-8">
                        <button
                            type="button"
                            @click="cancelEdit"
                            class="cursor-pointer rounded-lg bg-[#E2E8F0] px-6 py-2.5 text-xs font-bold text-slate-800 transition-colors hover:bg-[#CBD5E1]"
                        >
                            Batal Edit
                        </button>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="flex cursor-pointer items-center gap-2 rounded-lg bg-black px-6 py-2.5 text-xs font-bold text-white shadow-sm transition-colors hover:bg-amber-700 disabled:opacity-50"
                        >
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
    Check,
    ChevronLeft,
    ChevronRight,
    Trash2,
} from 'lucide-vue-next';

// 1. Dapatkan data riil dari Database Laravel melalui Props
const props = defineProps({
    employees: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({ totalStaff: 0, activeStaff: 0 }),
    },
});

defineOptions({
    layout: SidebarLayout,
});

// Component State
const activeTab = ref('SEMUA KARYAWAN');
const searchQuery = ref('');
const sortOrder = ref(
    typeof window !== 'undefined'
        ? (new URLSearchParams(window.location.search).get('sort') ?? 'latest')
        : 'latest',
);
const showPassword = ref(false);
const toastMessage = ref('');
const editingEmployee = ref(null); // Menyimpan objek karyawan yang sedang di-edit

const employeesPage = computed(() => props.employees ?? {});
const currentPage = computed(() => employeesPage.value.current_page ?? 1);
const lastPage = computed(() => employeesPage.value.last_page ?? 1);
const paginationTotal = computed(() => employeesPage.value.total ?? 0);
const paginationFrom = computed(() => employeesPage.value.from ?? 0);
const paginationTo = computed(() => employeesPage.value.to ?? 0);
const paginationPages = computed(() => {
    const totalPages = lastPage.value;
    const current = currentPage.value;

    if (totalPages <= 1) {
        return totalPages === 1 ? [{ key: 1, type: 'page', value: 1 }] : [];
    }

    if (totalPages <= 7) {
        return Array.from({ length: totalPages }, (_, index) => ({
            key: index + 1,
            type: 'page',
            value: index + 1,
        }));
    }

    const pages = [{ key: 1, type: 'page', value: 1 }];
    const start = Math.max(2, current - 1);
    const end = Math.min(totalPages - 1, current + 1);

    if (start > 2) {
        pages.push({ key: 'start-ellipsis', type: 'ellipsis' });
    }

    for (let page = start; page <= end; page += 1) {
        pages.push({ key: page, type: 'page', value: page });
    }

    if (end < totalPages - 1) {
        pages.push({ key: 'end-ellipsis', type: 'ellipsis' });
    }

    pages.push({ key: totalPages, type: 'page', value: totalPages });

    return pages;
});

// Form untuk Tambah Data Baru
const createForm = useForm({
    name: '',
    nip: '',
    password: '',
});

// Form untuk Edit Data
const editForm = useForm({
    id: null,
    name: '',
    nip: '',
    password: '',
});

// Computed Search Filter dari Props Employees
const visibleEmployees = computed(() => {
    const employees = employeesPage.value.data ?? [];

    if (!searchQuery.value.trim()) return employees;

    const query = searchQuery.value.toLowerCase();
    return employees.filter(
        (emp) =>
            emp.name?.toLowerCase().includes(query) ||
            emp.nip?.toLowerCase().includes(query),
    );
});

const goToPage = (page) => {
    if (page < 1 || page > lastPage.value || page === currentPage.value) {
        return;
    }

    router.get(
        '/pengaturan-akun',
        { page, sort: sortOrder.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['employees', 'stats'],
        },
    );
};

const handleSortChange = () => {
    router.get(
        '/pengaturan-akun',
        { page: 1, sort: sortOrder.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['employees', 'stats'],
        },
    );
};

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
    const chars =
        'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*';
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
        },
    });
};

// Update Employee
const updateEmployee = () => {
    editForm.put(`/employees/${editForm.id}`, {
        onSuccess: () => {
            showToast(`Data ${editForm.name} berhasil diperbarui!`);
            cancelEdit();
        },
    });
};

// Delete Employee
const deleteEmployee = (employee) => {
    if (
        confirm(
            `Apakah Anda yakin ingin menghapus akun karyawan "${employee.name}"?`,
        )
    ) {
        router.delete(`/employees/${employee.id}`, {
            onSuccess: () => {
                showToast(`Karyawan ${employee.name} berhasil dihapus!`);
            },
        });
    }
};
</script>
