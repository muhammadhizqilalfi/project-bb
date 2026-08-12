<template>
    <header
        class="relative flex h-16 shrink-0 items-center justify-end border-b border-slate-800/40 bg-[#0E1B2E] px-8 text-white shadow-sm"
    >
        <div
            class="relative flex cursor-pointer items-center gap-3 py-2"
            ref="menuWrapper"
            @mouseenter="openMenu"
            @mouseleave="closeMenu"
        >
            <div class="h-6 w-px bg-slate-700/80"></div>
            
            <!-- INFORMASI USER & CHEVRON ICON -->
            <div class="flex items-center gap-2 select-none">
                <div class="text-right">
                    <div class="text-xs font-bold tracking-wide text-white">
                        {{ user?.nama }}
                    </div>
                    <div
                        class="mt-0.5 font-mono text-[11px] tracking-tight text-slate-400"
                    >
                        {{ user?.nip ? `NIP. ${user.nip}` : '-' }}
                    </div>
                </div>

                <!-- INDICATOR CHEVRON -->
                <ChevronDown
                    class="h-4 w-4 text-slate-400 transition-transform duration-200"
                    :class="{ 'rotate-180 text-amber-400': isMenuOpen }"
                />
            </div>

            <!-- DROPDOWN MENU -->
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="transform opacity-0 scale-95 -translate-y-2"
                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                leave-to-class="transform opacity-0 scale-95 -translate-y-2"
            >
                <div
                    v-if="isMenuOpen"
                    class="absolute top-full right-0 z-50 mt-1 w-60 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl ring-1 ring-black/5"
                >
                    <!-- TOMBOL LOGOUT REDESIGN -->
                    <button
                        type="button"
                        @click="handleLogout"
                        class="group flex w-full cursor-pointer items-center justify-between rounded-lg px-3 py-2.5 text-left text-xs font-bold text-slate-700 transition-all duration-150 hover:bg-red-50 hover:text-red-600"
                    >
                        <div class="flex items-center gap-2.5">
                            <LogOut
                                class="h-4 w-4 text-slate-400 transition-transform duration-150 group-hover:scale-110 group-hover:text-red-500"
                            />
                            <span>Keluar Sesi (Logout)</span>
                        </div>
                    </button>
                </div>
            </Transition>
        </div>
    </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { LogOut, ChevronDown } from 'lucide-vue-next';

// 1. Panggil usePage untuk mengakses Shared Props dari Inertia
const page = usePage();

// 2. Buat computed property untuk mengambil data user
const user = computed(() => page.props.auth?.user);

const isMenuOpen = ref(false);
const menuWrapper = ref(null);

// Variable penampung timer delay
let closeTimer = null;

const openMenu = () => {
    // Jika ada timer penutupan yang sedang berjalan, batalkan!
    if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = null;
    }
    isMenuOpen.value = true;
};

const closeMenu = () => {
    // Memberikan jeda (250ms) sebelum menutup dropdown
    closeTimer = setTimeout(() => {
        isMenuOpen.value = false;
    }, 250);
};

const handleLogout = () => {
    if (closeTimer) clearTimeout(closeTimer);
    isMenuOpen.value = false;
    router.post('/logout');
};

const handleClickOutside = (event) => {
    if (menuWrapper.value && !menuWrapper.value.contains(event.target)) {
        if (closeTimer) clearTimeout(closeTimer);
        isMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    if (closeTimer) clearTimeout(closeTimer);
    document.removeEventListener('click', handleClickOutside);
});
</script>