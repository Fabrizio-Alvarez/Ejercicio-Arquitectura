<script setup>
import { Link } from '@inertiajs/vue3';
import { useFormato } from '../../../composables/useFormato.js';
import { emojiProducto } from '../../../constants/emojis.js';
import { etiquetaMetodoPago, etiquetaEstado } from '../../../constants/etiquetas.js';

defineProps({
    pedido: { type: Object, required: true },
});

const formato = useFormato();
</script>

<template>
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center gap-2 text-sm text-slate-500">
            <Link href="/tienda/cuenta/pedidos" class="hover:text-emerald-600">Mis pedidos</Link>
            <span>/</span>
            <span class="text-slate-700">#{{ pedido.id.slice(0, 8) }}</span>
        </nav>

        <!-- Header -->
        <div class="rounded-xl bg-white p-6 shadow-sm mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Pedido #{{ pedido.id.slice(0, 8) }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ pedido.fecha }}</p>
                </div>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                    :class="pedido.estado === 'confirmada' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                >
                    {{ etiquetaEstado?.[pedido.estado] ?? pedido.estado }}
                </span>
            </div>
        </div>

        <!-- Items -->
        <div class="rounded-xl bg-white p-6 shadow-sm mb-4">
            <h2 class="font-semibold text-slate-800 mb-4">Productos</h2>
            <div class="space-y-3">
                <div v-for="(item, i) in pedido.items" :key="i" class="flex items-center gap-3">
                    <span class="text-2xl">{{ emojiProducto(item.productName) }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-800">{{ item.productName }}</p>
                        <p class="text-xs text-slate-400">{{ item.quantity }} × {{ formato.dinero(item.unitPrice * 100) }}</p>
                    </div>
                    <span class="font-medium text-slate-800">{{ formato.dinero(item.unitPrice * item.quantity * 100) }}</span>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="rounded-xl bg-white p-6 shadow-sm space-y-3">
            <div class="flex justify-between text-sm text-slate-500">
                <span>Comprador</span>
                <span class="text-slate-700 font-medium">{{ pedido.customerName }}</span>
            </div>
            <div class="flex justify-between text-sm text-slate-500">
                <span>Método de pago</span>
                <span class="text-slate-700 font-medium">{{ etiquetaMetodoPago[pedido.metodoDePago] }}</span>
            </div>
            <hr class="border-slate-100" />
            <div class="flex justify-between font-bold text-lg">
                <span>Total</span>
                <span class="text-emerald-600">{{ formato.dinero(pedido.total * 100) }} {{ pedido.moneda }}</span>
            </div>
        </div>

        <div class="mt-6 text-center">
            <Link href="/tienda/cuenta/pedidos" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">
                ← Volver a mis pedidos
            </Link>
        </div>
    </div>
</template>
