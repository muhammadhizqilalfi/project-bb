<template>
    <!-- Parent container dikunci selayar penuh (h-screen w-screen overflow-hidden) -->
    <div class="h-screen w-screen flex overflow-hidden bg-[#F4F6F9] font-sans antialiased text-slate-800 select-none">
        <!-- Sidebar Navigation (Terkunci / Diam di kiri) -->
        <Sidebar
            :user-role="userRole"
            :active-menu="activeMenu"
            @update:active-menu="$emit('update:activeMenu', $event)"
            class="shrink-0 h-full"
        />

        <!-- Main Wrapper (Area Kanan) -->
        <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden">
            <!-- Topbar Header (Terkunci / Diam di atas) -->
            <Topbar
                :user-name="userName"
                :nip="nip"
                class="shrink-0"
            />

            <!-- Page Main Content Slot (HANYA BAGIAN INI YANG BISA DI-SCROLL) -->
            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import Topbar from '@/Components/Topbar.vue';

defineProps({
    userRole: {
        type: String,
        default: 'karyawan',
    },
    activeMenu: {
        type: String,
        default: 'FORM',
    },
    userName: {
        type: String,
        default: '',
    },
    nip: {
        type: String,
        default: '',
    },
});

defineEmits(['update:activeMenu']);
</script>