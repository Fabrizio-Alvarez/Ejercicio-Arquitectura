<script setup>
import { useForm } from '@inertiajs/vue3';

defineProps({
    perfiles: { type: Array, default: () => [] },
});

const form = useForm({ perfil: '' });

function elegir(value) {
    form.perfil = value;
    form.post('/iniciar');
}
</script>

<template>
    <section class="mx-auto max-w-3xl px-4 py-16">
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-slate-900">🛒 Supermercado</h1>
            <p class="mt-2 text-slate-500">Elegí tu perfil para continuar</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <button
                v-for="perfil in perfiles"
                :key="perfil.value"
                type="button"
                :disabled="form.processing"
                @click="elegir(perfil.value)"
                class="rounded-lg border border-slate-200 bg-white p-6 text-left shadow-sm transition hover:border-emerald-400 hover:shadow disabled:opacity-50"
            >
                <div class="text-lg font-semibold text-slate-900">{{ perfil.etiqueta }}</div>
                <p class="mt-1 text-sm text-slate-500">{{ perfil.descripcion }}</p>
            </button>
        </div>
    </section>
</template>
