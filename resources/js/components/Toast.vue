<script setup>
import { useToast } from '../composables/useToast.js';

const { toasts } = useToast();

const styles = {
    success: { bg: 'bg-emerald-600', icon: '✓' },
    error: { bg: 'bg-red-600', icon: '✕' },
    info: { bg: 'bg-slate-800', icon: 'ℹ' },
};
</script>

<template>
    <Teleport to="body">
        <TransitionGroup
            tag="div"
            move-class="transition-all duration-300 ease-out"
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-300 ease-in"
            enter-from-class="opacity-0 translate-y-3"
            enter-to-class="opacity-100 translate-y-0"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 translate-x-6"
            class="fixed bottom-6 right-6 z-[60] flex flex-col gap-3"
        >
            <div
                v-for="t in toasts"
                :key="t.id"
                class="flex items-center gap-3 rounded-xl px-4 py-3 shadow-lg text-white text-sm font-medium"
                :class="styles[t.type]?.bg ?? styles.info.bg"
            >
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white/25 text-xs font-bold">
                    {{ styles[t.type]?.icon ?? styles.info.icon }}
                </span>
                {{ t.message }}
            </div>
        </TransitionGroup>
    </Teleport>
</template>
