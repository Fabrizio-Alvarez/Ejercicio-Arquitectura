<script setup>
import { Link } from '@inertiajs/vue3';
import { useFormato } from '../../composables/useFormato.js';
import { etiquetaMetodoPago } from '../../constants/etiquetas.js';

defineProps({
    venta: { type: Object, required: true },
});

const formato = useFormato();
</script>

<template>
    <div class="mx-auto max-w-2xl px-4 py-16 text-center">
        <!-- Success animation -->
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100">
            <svg class="h-10 w-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="mt-6 text-3xl font-bold text-slate-800">¡Compra confirmada!</h1>
        <p class="mt-2 text-slate-500">Tu pedido fue procesado correctamente.</p>

        <!-- Order details -->
        <div class="mt-8 rounded-xl bg-white p-6 shadow-sm text-left">
            <div class="flex items-center justify-between border-b pb-4">
                <span class="text-sm text-slate-500">Pedido</span>
                <span class="font-mono font-semibold text-slate-800">#{{ venta.id.slice(0, 8) }}</span>
            </div>

            <div class="py-4 space-y-2">
                <div v-for="(item, i) in venta.items" :key="i" class="flex justify-between text-sm">
                    <span class="text-slate-600">{{ item.quantity }} × {{ item.productName }}</span>
                    <span class="font-medium text-slate-800">{{ formato.dinero(item.unitPrice * item.quantity * 100) }}</span>
                </div>
            </div>

            <div class="space-y-2 border-t pt-4 text-sm">
                <div class="flex justify-between text-slate-500">
                    <span>Comprador</span>
                    <span class="text-slate-700">{{ venta.customerName }}</span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Método de pago</span>
                    <span class="text-slate-700">{{ etiquetaMetodoPago[venta.paymentMethod] ?? venta.paymentMethod }}</span>
                </div>
            </div>

            <div class="mt-4 flex justify-between border-t pt-4 font-bold text-lg">
                <span>Total</span>
                <span>{{ formato.dinero(venta.total * 100) }} {{ venta.moneda }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex justify-center gap-4">
            <Link href="/tienda/catalogo" class="rounded-xl bg-emerald-600 px-6 py-3 font-semibold text-white hover:bg-emerald-700 transition-colors">
                Seguir comprando
            </Link>
            <Link href="/tienda" class="rounded-xl border border-slate-300 px-6 py-3 font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Volver al inicio
            </Link>
        </div>
    </div>
</template>
