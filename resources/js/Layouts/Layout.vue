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

defineEmits(['update:activeMenu']);
</script>