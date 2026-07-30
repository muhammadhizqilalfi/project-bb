<template>
    <header
        class="relative flex h-16 shrink-0 items-center justify-end border-b border-slate-800/40 bg-[#0E1B2E] px-8 text-white shadow-sm"
    >
        <div
            class="relative flex cursor-pointer items-center gap-4"
            ref="menuWrapper"
            @mouseenter="openMenu"
            @mouseleave="closeMenu"
        >
            <div class="h-6 w-px bg-slate-700/80"></div>
            <div class="text-right">
                <div class="text-xs font-bold tracking-wide text-white">
                    {{ user?.nama}}
                </div>
                <div
                    class="mt-0.5 font-mono text-[11px] tracking-tight text-slate-400"
                >
                    {{ user?.nip ? `NIP. ${user.nip}` : '-' }}
                </div>
            </div>

            <Transition
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="transform opacity-0 scale-95 -translate-y-1"
                enter-to-class="transform opacity-100 scale-100 translate-y-0"
                leave-active-class="transition duration-120 ease-in"
                leave-from-class="transform opacity-100 scale-100 translate-y-0"
                leave-to-class="transform opacity-0 scale-95 -translate-y-1"
            >
                <div
                    v-if="isMenuOpen"
                    class="absolute top-full right-0 z-50 mt-3 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_18px_40px_rgba(15,23,42,0.12)]"
                >
                    <button
                        type="button"
                        @click="handleLogout"
                        class="flex w-full cursor-pointer items-center gap-3 px-4 py-3 text-left text-sm text-slate-600 transition-colors hover:bg-red-300 hover:text-slate-900"
                    >
                        <LogOut class="h-4 w-4 text-slate-500" />
                        <span>Logout</span>
                    </button>
                </div>
            </Transition>
        </div>
    </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { LogOut } from 'lucide-vue-next';

// 1. Panggil usePage untuk mengakses Shared Props dari Inertia
const page = usePage();

// 2. Buat computed property untuk mengambil data user
const user = computed(() => page.props.auth?.user);

const isMenuOpen = ref(false);
const menuWrapper = ref(null);

const openMenu = () => {
    isMenuOpen.value = true;
};

const closeMenu = () => {
    isMenuOpen.value = false;
};

const handleLogout = () => {
    isMenuOpen.value = false;
    router.post('/logout');
};

const handleClickOutside = (event) => {
    if (menuWrapper.value && !menuWrapper.value.contains(event.target)) {
        isMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>