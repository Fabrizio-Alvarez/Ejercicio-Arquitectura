<script setup>
import { Link } from '@inertiajs/vue3';
import { useFormato } from '../../../composables/useFormato.js';
import { etiquetaMetodoPago, etiquetaEstado } from '../../../constants/etiquetas.js';

defineProps({
    pedidos: { type: Array, default: () => [] },
});

const formato = useFormato();
</script>

<template>
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Mis pedidos</h1>

        <!-- Empty state -->
        <div v-if="pedidos.length === 0" class="text-center py-20">
            <span class="text-6xl block mb-4 opacity-30">📦</span>
            <p class="text-lg text-slate-400">Todavía no tenés pedidos.</p>
            <Link href="/tienda/catalogo" class="mt-4 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                Ir al catálogo
            </Link>
        </div>

        <!-- Orders list -->
        <div v-else class="space-y-3">
            <Link
                v-for="p in pedidos"
                :key="p.id"
                :href="`/tienda/cuenta/pedidos/${p.id}`"
                class="step-fade flex items-center gap-4 rounded-xl bg-white p-5 shadow-sm border border-slate-200 hover:border-emerald-300 hover:shadow-md transition-all"
            >
                <!-- Icon -->
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-xl">
                    📦
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-800 truncate">Pedido #{{ p.id.slice(0, 8) }}</p>
                    <p class="text-sm text-slate-500">{{ p.fecha }} · {{ p.itemsCount }} {{ p.itemsCount === 1 ? 'producto' : 'productos' }}</p>
                </div>

                <!-- Payment + status -->
                <div class="hidden sm:flex flex-col items-end gap-1">
                    <span class="text-sm text-slate-500">{{ etiquetaMetodoPago[p.metodoDePago] }}</span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="p.estado === 'confirmada' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                    >
                        {{ etiquetaEstado?.[p.estado] ?? p.estado }}
                    </span>
                </div>

                <!-- Total -->
                <div class="text-right">
                    <p class="font-bold text-slate-800">{{ formato.dinero(p.total * 100) }}</p>
                    <p class="text-xs text-slate-400">{{ p.moneda }}</p>
                </div>

                <svg class="w-5 h-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </Link>
        </div>
    </div>
</template>
