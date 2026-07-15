<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useCart } from '../../composables/useCart.js';
import { useFormato } from '../../composables/useFormato.js';
import { etiquetaMetodoPago } from '../../constants/etiquetas.js';

defineProps({
    metodosDePago: { type: Array, default: () => [] },
});

const { items, subtotal, clear } = useCart();
const formato = useFormato();

const paso = ref(1); // 1=datos, 2=pago, 3=review

const form = useForm({
    customerName: '',
    paymentMethod: 'efectivo',
    items: [],
});

function avanzar() {
    if (paso.value === 1 && !form.customerName.trim()) return;
    paso.value++;
}

function retroceder() {
    if (paso.value > 1) paso.value--;
}

function submit() {
    form.items = items.map((i) => ({
        productId: i.id,
        quantity: i.quantity,
        productName: i.nombre,
        unitPrice: i.precio,
    }));
    form.post('/tienda/checkout', {
        onSuccess: () => clear(),
    });
}

const total = computed(() => subtotal.value);
</script>

<template>
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Checkout</h1>

        <!-- Empty cart -->
        <div v-if="items.length === 0" class="text-center py-20">
            <p class="text-lg text-slate-400">Tu carrito está vacío.</p>
            <Link href="/tienda/catalogo" class="mt-4 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Ir al catálogo
            </Link>
        </div>

        <div v-else class="space-y-6">
            <!-- Stepper -->
            <div class="flex items-center justify-center gap-2">
                <template v-for="n in 3" :key="n">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm transition-colors"
                        :class="paso >= n ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-400'"
                    >{{ n }}</div>
                    <div v-if="n < 3" class="w-12 h-0.5" :class="paso > n ? 'bg-emerald-600' : 'bg-slate-200'"></div>
                </template>
            </div>

            <!-- Step 1: Datos -->
            <div v-if="paso === 1" class="rounded-xl bg-white p-6 shadow-sm space-y-4">
                <h2 class="font-semibold text-slate-800">Tus datos</h2>
                <label class="block">
                    <span class="text-sm text-slate-600">Nombre del comprador</span>
                    <input
                        v-model="form.customerName"
                        type="text"
                        placeholder="Tu nombre"
                        class="mt-1 w-full rounded-lg border border-slate-300 py-2.5 px-4 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none"
                    />
                </label>
                <button
                    @click="avanzar"
                    :disabled="!form.customerName.trim()"
                    class="w-full rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700 disabled:bg-slate-300 transition-colors"
                >
                    Continuar
                </button>
            </div>

            <!-- Step 2: Pago -->
            <div v-if="paso === 2" class="rounded-xl bg-white p-6 shadow-sm space-y-4">
                <h2 class="font-semibold text-slate-800">Método de pago</h2>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        v-for="m in metodosDePago"
                        :key="m.value"
                        @click="form.paymentMethod = m.value"
                        class="rounded-lg border-2 py-3 px-4 text-sm font-medium transition-colors text-left"
                        :class="form.paymentMethod === m.value
                            ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                            : 'border-slate-200 text-slate-600 hover:border-slate-300'"
                    >
                        {{ etiquetaMetodoPago[m.value] ?? m.label }}
                    </button>
                </div>
                <div class="flex gap-3">
                    <button @click="retroceder" class="rounded-lg border border-slate-300 px-4 py-3 font-medium text-slate-600 hover:bg-slate-50">
                        Volver
                    </button>
                    <button @click="avanzar" class="flex-1 rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700">
                        Continuar
                    </button>
                </div>
            </div>

            <!-- Step 3: Revisar -->
            <div v-if="paso === 3" class="rounded-xl bg-white p-6 shadow-sm space-y-4">
                <h2 class="font-semibold text-slate-800">Revisá tu pedido</h2>

                <div class="space-y-2">
                    <div v-for="item in items" :key="item.id" class="flex justify-between text-sm">
                        <span class="text-slate-600">{{ item.quantity }} × {{ item.nombre }}</span>
                        <span class="font-medium text-slate-800">{{ formato.dinero(item.precio * item.quantity * 100) }}</span>
                    </div>
                </div>

                <hr />

                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-slate-500">
                        <span>Comprador</span>
                        <span class="text-slate-700">{{ form.customerName }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Pago</span>
                        <span class="text-slate-700">{{ etiquetaMetodoPago[form.paymentMethod] }}</span>
                    </div>
                </div>

                <hr />

                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span>{{ formato.dinero(total * 100) }} ARS</span>
                </div>

                <div class="flex gap-3">
                    <button @click="retroceder" class="rounded-lg border border-slate-300 px-4 py-3 font-medium text-slate-600 hover:bg-slate-50">
                        Volver
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="flex-1 rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? 'Procesando…' : 'Confirmar compra' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
