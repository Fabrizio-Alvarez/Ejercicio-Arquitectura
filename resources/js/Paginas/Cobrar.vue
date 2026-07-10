<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    productos: { type: Array, default: () => [] },
    metodosDePago: { type: Array, default: () => [] },
});

const form = ref({
    productoId: props.productos[0]?.id ?? '',
    cantidad: 1,
    metodoDePago: 'efectivo',
    cajeroId: 'cajero-1',
    cliente: '',
});

const venta = ref(null);
const error = ref(null);
const enviando = ref(false);

async function cobrar() {
    error.value = null;
    venta.value = null;
    enviando.value = true;

    try {
        const res = await fetch('/api/checkout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
            body: JSON.stringify({
                saleId: crypto.randomUUID(),
                cashierId: form.value.cajeroId,
                customerName: form.value.cliente || 'Consumidor Final',
                paymentMethod: form.value.metodoDePago,
                items: [{ productId: form.value.productoId, quantity: Number(form.value.cantidad) }],
            }),
        });

        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'No se pudo registrar la venta');
        }

        venta.value = data;
        // Refresca stock y movimientos para reflejar el descuento y la auditoría.
        router.reload({ only: [] });
    } catch (e) {
        error.value = e.message;
    } finally {
        enviando.value = false;
    }
}
</script>

<template>
    <section class="grid gap-8 md:grid-cols-2">
        <div>
            <h2 class="text-2xl font-bold mb-6">Registrar venta</h2>

            <form @submit.prevent="cobrar" class="space-y-4 bg-white p-6 rounded-lg shadow-sm">
                <label class="block">
                    <span class="text-sm text-slate-600">Producto</span>
                    <select v-model="form.productoId" class="mt-1 w-full rounded border-slate-300">
                        <option v-for="p in productos" :key="p.id" :value="p.id">
                            {{ p.nombre }} — {{ p.precio.toFixed(2) }} {{ p.moneda }}
                        </option>
                    </select>
                </label>

                <label class="block">
                    <span class="text-sm text-slate-600">Cantidad</span>
                    <input v-model.number="form.cantidad" type="number" min="1" class="mt-1 w-full rounded border-slate-300" />
                </label>

                <label class="block">
                    <span class="text-sm text-slate-600">Método de pago</span>
                    <select v-model="form.metodoDePago" class="mt-1 w-full rounded border-slate-300">
                        <option v-for="m in metodosDePago" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                </label>

                <div class="grid grid-cols-2 gap-4">
                    <label class="block">
                        <span class="text-sm text-slate-600">Cajero</span>
                        <input v-model="form.cajeroId" class="mt-1 w-full rounded border-slate-300" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600">Cliente</span>
                        <input v-model="form.cliente" placeholder="Consumidor Final" class="mt-1 w-full rounded border-slate-300" />
                    </label>
                </div>

                <button :disabled="enviando || !form.productoId" class="w-full rounded bg-emerald-600 text-white py-2 font-semibold hover:bg-emerald-700 disabled:opacity-50">
                    {{ enviando ? 'Cobrando…' : 'Cobrar' }}
                </button>

                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
            </form>
        </div>

        <div v-if="venta">
            <h2 class="text-2xl font-bold mb-6">Venta #{{ venta.id.slice(0, 8) }}</h2>
            <div class="bg-white p-6 rounded-lg shadow-sm space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Cliente</span>
                    <span>{{ venta.customerName }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Pago</span>
                    <span>{{ venta.paymentMethod }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Estado</span>
                    <span>{{ venta.status }}</span>
                </div>
                <hr />
                <ul class="text-sm space-y-1">
                    <li v-for="line in venta.lines" :key="line.productId" class="flex justify-between">
                        <span>{{ line.quantity }} × {{ line.productName }}</span>
                        <span>{{ (line.unitPrice.amount / 100).toFixed(2) }} {{ line.unitPrice.currency }}</span>
                    </li>
                </ul>
                <hr />
                <div class="flex justify-between font-bold">
                    <span>Total</span>
                    <span>{{ (venta.total.amount / 100).toFixed(2) }} {{ venta.total.currency }}</span>
                </div>
            </div>
        </div>
    </section>
</template>
