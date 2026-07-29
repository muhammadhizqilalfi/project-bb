<template>
    <Head title="Login - Sistem ..." />
    <div class="min-h-screen grid place-items-center bg-[#F8F9FA] p-4 relative overflow-hidden">
        <!-- Watermark Background Logo -->
        <img
            src="/images/logo.png"
            alt=""
            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] opacity-[0.08] z-0 pointer-events-none select-none grayscale"
            draggable="false"
            aria-hidden="true"
        />
        <div
            class="max-w-[420px] w-full bg-white rounded-2xl shadow-xl overflow-hidden relative z-10 border border-gray-100"
        >
            <!-- Top Accent Line -->
            <div class="h-2 bg-[#FFD000] w-full"></div>

            <!-- Card Content -->
            <div class="px-8 pt-8 pb-6">
                <!-- Header: Logo & Badge -->
                <div class="flex flex-col items-center">
                    <!-- Kejaksaan RI Logo Emblem -->
                    <div class="w-20 h-20 mb-3 flex items-center justify-center">
                        <img 
                            src="/images/logo.png" 
                            alt="Logo Kejaksaan RI" 
                            class="w-full h-full object-contain" 
                        />
                    </div>

                    <!-- Institution Badge -->
                    <span
                        class="inline-flex items-center gap-1.5 bg-[#E6F4EA] text-[#0F5132] text-xs font-semibold rounded-full px-3 py-1 my-2"
                    >
                        KEJAKSAAN RI
                    </span>

                    <!-- Main Heading -->
                    <h1 class="text-xl font-bold text-gray-800 text-center my-2 tracking-tight leading-snug">
                        Sistem Rekapitulasi<br />Barang Bukti
                    </h1>
                </div>

                <!-- Notice Banner -->
                <div
                    class="bg-[#E8F0FE] text-[#1967D2] rounded-lg p-3 text-xs flex items-start gap-2 my-3"
                >
                    <!-- Info Icon -->
                    <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <p>
                        Akses terbatas hanya untuk
                        <strong>Staf BB &amp; Administrator</strong> yang
                        memiliki izin resmi.
                    </p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="handleLogin">
                    <!-- Field NIP / USER ID -->
                    <div class="mb-4">
                        <label
                            for="nip"
                            class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1 block"
                        >
                            NIP / USER ID
                        </label>
                        <div
                            class="bg-[#F1F3F4] border border-gray-200 rounded-lg flex items-center px-3 py-2.5 transition-all duration-200 focus-within:ring-2 focus-within:ring-[#FFD000]"
                        >
                            <!-- ID Card Icon -->
                            <svg class="w-4 h-4 text-gray-400 mr-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="5" rx="2"/>
                                <path d="M2 10h20"/>
                                <path d="M6 15h2"/>
                                <path d="M14 15h4"/>
                            </svg>
                            <input
                                id="nip"
                                v-model="nip"
                                type="text"
                                placeholder="Masukkan NIP pegawai..."
                                class="bg-transparent text-sm w-full outline-none text-gray-800 placeholder-gray-400"
                            />
                        </div>
                    </div>

                    <!-- Field PASSWORD -->
                    <div class="mb-2">
                        <div class="flex items-center justify-between mb-1">
                            <label
                                for="password"
                                class="text-[11px] font-bold text-gray-500 uppercase tracking-wider"
                            >
                                PASSWORD
                            </label>
                        </div>
                        <div
                            class="bg-[#F1F3F4] border border-gray-200 rounded-lg flex items-center px-3 py-2.5 transition-all duration-200 focus-within:ring-2 focus-within:ring-[#FFD000]"
                        >
                            <!-- Lock Icon -->
                            <svg class="w-4 h-4 text-gray-400 mr-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input
                                id="password"
                                v-model="password"
                                type="password"
                                placeholder="••••••••"
                                class="bg-transparent text-sm w-full outline-none text-gray-800 placeholder-gray-400"
                            />
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="w-full bg-[#FFD000] hover:bg-[#E6C200] text-gray-900 font-bold py-3 rounded-lg text-sm flex items-center justify-center gap-2 mt-5 shadow-sm transition-all duration-200 active:scale-[0.99] cursor-pointer"
                    >
                        Masuk Portal
                        <!-- Arrow Icon -->
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                    </button>
                </form>

                <!-- Footer Section -->
                <div class="border-t border-gray-100 my-4"></div>
                <p class="text-xs text-center text-gray-500">
                    Kendala akses?
                    <a
                        href="#"
                        class="text-yellow-200 hover:underline font-medium cursor-pointer"
                    >
                        Hubungi Admin Kejaksaan
                    </a>
                </p>
                <span
                    class="text-[10px] text-center text-gray-400 tracking-wider font-mono mt-1 uppercase block"
                >
                    V4.2.0 • SECURE ENTERPRISE ACCESS
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>

import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: null,
});

const nip = ref('');
const password = ref('');

const handleLogin = () => {
    console.log('Login attempt:', { nip: nip.value, password: password.value });
    // TODO: Implementasi logika login
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:deep(*) {
    font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
}
</style>