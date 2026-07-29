<template>
    <header class="h-16 bg-[#0E1B2E] px-8 flex items-center justify-end text-white shrink-0 shadow-sm border-b border-slate-800/40">
        <!-- User Profile & NIP display -->
        <div class="flex items-center gap-4">
            <div class="h-6 w-px bg-slate-700/80"></div>
            <div class="text-right">
                <div class="text-xs font-bold tracking-wide text-white">{{ userName }}</div>
                <div class="text-[11px] text-slate-400 font-mono tracking-tight mt-0.5">{{ nip }}</div>
            </div>
        </div>
    </header>
</template>

<script setup>
defineProps({
    userName: {
        type: String,
        default: 'ADMIN KEJAKSAAN'
    },
    nip: {
        type: String,
        default: 'NIP. 19820412 200501 1 002'
    }
});
</script>
