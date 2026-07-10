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
        router.reload({ only: [] });
    } catch (e) {
        error.value = e.message;
    } finally {
        enviando.value = false;
    }
}

// Cierre de caja del día para el cajero actual.
const cierre = ref({
    cajeroId: form.value.cajeroId,
    fecha: new Date().toISOString().slice(0, 10),
});
const resultadoCierre = ref(null);
const cierreError = ref(null);
const consultando = ref(false);

async function consultarCierre() {
    cierreError.value = null;
    resultadoCierre.value = null;
    consultando.value = true;

    try {
        const params = new URLSearchParams({
            cashierId: cierre.value.cajeroId,
            date: cierre.value.fecha,
        });
        const res = await fetch(`/api/cash-close?${params}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'No se pudo obtener el cierre');
        }
        resultadoCierre.value = data;
    } catch (e) {
        cierreError.value = e.message;
    } finally {
        consultando.value = false;
    }
}

function formato(dinero) {
    return dinero ? `${(dinero.amount / 100).toFixed(2)} ${dinero.currency}` : '—';
}
</script>

<template>
    <section class="space-y-8">
        <div class="grid gap-8 md:grid-cols-2">
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
        </div>

        <div>
            <h2 class="text-2xl font-bold mb-6">Cierre de caja</h2>
            <form @submit.prevent="consultarCierre" class="space-y-4 bg-white p-6 rounded-lg shadow-sm">
                <div class="grid grid-cols-2 gap-4">
                    <label class="block">
                        <span class="text-sm text-slate-600">Cajero</span>
                        <input v-model="cierre.cajeroId" class="mt-1 w-full rounded border-slate-300" />
                    </label>
                    <label class="block">
                        <span class="text-sm text-slate-600">Día</span>
                        <input v-model="cierre.fecha" type="date" class="mt-1 w-full rounded border-slate-300" />
                    </label>
                </div>
                <button :disabled="consultando" class="rounded bg-slate-800 px-4 py-2 font-semibold text-white hover:bg-slate-900 disabled:opacity-50">
                    {{ consultando ? 'Consultando…' : 'Consultar cierre' }}
                </button>
                <p v-if="cierreError" class="text-sm text-red-600">{{ cierreError }}</p>
            </form>

            <div v-if="resultadoCierre" class="mt-6 bg-white p-6 rounded-lg shadow-sm space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Ventas del día</span>
                    <span>{{ resultadoCierre.count }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Total</span>
                    <span class="font-bold">{{ formato(resultadoCierre.total) }}</span>
                </div>
                <hr v-if="resultadoCierre.rows.length" />
                <ul v-if="resultadoCierre.rows.length" class="text-sm space-y-1">
                    <li v-for="(row, i) in resultadoCierre.rows" :key="i" class="flex justify-between">
                        <span>{{ row.customerName }}</span>
                        <span>{{ formato(row.amount) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
