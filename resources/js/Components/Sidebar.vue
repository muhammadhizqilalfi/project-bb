    <template>
        <aside class="w-64 bg-[#0E1B2E] text-white flex flex-col shrink-0 min-h-screen z-20 shadow-xl">
            <!-- Sidebar Brand Header -->
            <div class="h-16 px-6 flex items-center gap-3.5 border-b border-slate-800/60">
                <div class="w-9 h-9 flex items-center justify-center shrink-0">
                    <img src="/images/logo.png" alt="Kejaksaan RI" class="w-full h-full object-contain drop-shadow" />
                </div>
                <span class="font-extrabold text-base tracking-wider text-white">KEJAKSAAN</span>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-1 px-3.5 py-6 space-y-1.5 overflow-y-auto">

                <!-- BERANDA -->
                <button type="button" @click="selectMenu('BERANDA')" :class="[
                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-xs tracking-wider uppercase transition-all duration-150 cursor-pointer',
                    activeMenu === 'BERANDA'
                        ? 'bg-[#FFD000] text-slate-950 shadow-md shadow-yellow-500/10'
                        : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'
                ]">
                    <LayoutGrid class="w-4 h-4 shrink-0" />
                    <span>BERANDA</span>
                </button>

                <!-- FORM (Dropdown) -->
                <div>
                    <button type="button" @click="isFormDropdownOpen = !isFormDropdownOpen"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg font-bold text-xs tracking-wider uppercase text-slate-300 hover:bg-slate-800/70 hover:text-white transition-colors cursor-pointer">
                        <div class="flex items-center gap-3">
                            <FileText class="w-4 h-4 shrink-0" />
                            <span>FORM</span>
                        </div>
                        <ChevronDown :class="[
                            'w-4 h-4 transition-transform duration-200 text-slate-400',
                            isFormDropdownOpen ? 'rotate-180' : ''
                        ]" />
                    </button>

                    <!-- Submenu Items -->
                    <Transition enter-active-class="transition duration-150 ease-out"
                        enter-from-class="transform scale-95 opacity-0 -translate-y-1"
                        enter-to-class="transform scale-100 opacity-100 translate-y-0"
                        leave-active-class="transition duration-100 ease-in"
                        leave-from-class="transform scale-100 opacity-100 translate-y-0"
                        leave-to-class="transform scale-95 opacity-0 -translate-y-1">
                        <div v-show="isFormDropdownOpen" @click="selectMenu('FORM')" :class="['pl-11 pr-3 py-1 space-y-1', activeMenu === 'FORM' ? 'bg-slate-800/70 rounded-lg' : '']">
                            <Link href="form3a"
                                class="block py-2 px-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors rounded">
                                FORM 3A
                            </Link>
                            <Link href="form3b"
                                class="block py-2 px-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors rounded">
                                FORM 3B
                            </Link>
                            <Link href="form3c"
                                class="block py-2 px-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors rounded">
                                FORM 3C
                            </Link>
                        </div>
                    </Transition>
                </div>

                <!-- LAPORAN -->
                <button type="button" @click="selectMenu('LAPORAN')" :class="[
                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-xs tracking-wider uppercase transition-colors cursor-pointer',
                    activeMenu === 'LAPORAN'
                        ? 'bg-[#FFD000] text-slate-950 shadow-md'
                        : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'
                ]">
                    <BarChart3 class="w-4 h-4 shrink-0" />
                    <span>LAPORAN</span>
                </button>

                <!-- PENGATURAN FORM -->
                <button type="button" @click="selectMenu('PENGATURAN FORM')" :class="[
                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-xs tracking-wider uppercase transition-colors cursor-pointer',
                    activeMenu === 'PENGATURAN FORM'
                        ? 'bg-[#FFD000] text-slate-950 shadow-md'
                        : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'
                ]">
                    <Sliders class="w-4 h-4 shrink-0" />
                    <span>PENGATURAN FORM</span>
                </button>

                <!-- PENGATURAN AKUN -->
                <button type="button" @click="selectMenu('PENGATURAN AKUN')" :class="[
                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg font-bold text-xs tracking-wider uppercase transition-colors cursor-pointer',
                    activeMenu === 'PENGATURAN AKUN'
                        ? 'bg-[#FFD000] text-slate-950 shadow-md'
                        : 'text-slate-300 hover:bg-slate-800/70 hover:text-white'
                ]">
                    <Users class="w-4 h-4 shrink-0" />
                    <span>PENGATURAN AKUN</span>
                </button>
            </nav>
        </aside>
    </template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import {
    LayoutGrid,
    FileText,
    BarChart3,
    Sliders,
    Users,
    ChevronDown
} from 'lucide-vue-next';

const page = usePage();

const activeMenu = computed(() => {
    const url = page.url;

    if (url.startsWith('/pengaturan-akun')) return 'PENGATURAN AKUN';
    if (url.startsWith('/laporan')) return 'LAPORAN';
    if (url.startsWith('/pengaturan-form')) return 'PENGATURAN FORM';
    if (url.startsWith('/form3a') || url.startsWith('/form3b') || url.startsWith('/form3c')) return 'FORM';

    // return default menu if none of the above matches
    return 'BERANDA';
});

const isFormDropdownOpen = ref(true);

const selectMenu = (menuName) => {
    if (menuName === 'BERANDA') {
        router.get('/dashboard');
    } else if (menuName === 'PENGATURAN AKUN') {
        router.get('/pengaturan-akun');
    } else if (menuName === 'LAPORAN') {
        router.get('/laporan');
    } else if (menuName === 'PENGATURAN FORM') {
        router.get('/pengaturan-form');
    }
};
</script>