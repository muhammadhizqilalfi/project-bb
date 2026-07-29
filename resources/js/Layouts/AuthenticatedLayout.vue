<template>
    <div class="min-h-screen flex bg-[#F4F6F9] font-sans antialiased text-slate-800 select-none">
        <!-- Sidebar Navigation -->
        <Sidebar
            :user-role="userRole"
            :active-menu="activeMenu"
            @update:active-menu="$emit('update:activeMenu', $event)"
        />

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar Header -->
            <Topbar
                :user-name="userName"
                :nip="nip"
            />

            <!-- Page Main Content Slot -->
            <div class="flex-1 flex flex-col min-h-0 overflow-y-auto">
                <slot />
            </div>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import Topbar from '@/Components/Topbar.vue';

defineProps({
    userRole: {
        type: String,
        default: 'karyawan' // 'karyawan' atau 'admin'
    },
    activeMenu: {
        type: String,
        default: 'BERANDA'
    },
    userName: {
        type: String,
        default: 'ADMIN KEJAKSAAN'
    },
    nip: {
        type: String,
        default: 'NIP. 19820412 200501 1 002'
    }
});

defineEmits(['update:activeMenu']);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:deep(*) {
    font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
}
</style>