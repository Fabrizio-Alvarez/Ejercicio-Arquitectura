<script setup>
import { ref, computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useCart } from '../../composables/useCart.js';
import { useFormato } from '../../composables/useFormato.js';
import { useToast } from '../../composables/useToast.js';
import { emojiProducto } from '../../constants/emojis.js';
import { etiquetaMetodoPago } from '../../constants/etiquetas.js';

defineProps({
    metodosDePago: { type: Array, default: () => [] },
});

const { items, subtotal, clear } = useCart();
const formato = useFormato();
const toast = useToast();

const paso = ref(1); // 1=datos, 2=pago, 3=review

const form = useForm({
    customerName: '',
    paymentMethod: 'efectivo',
    items: [],
});

const pasos = ['Datos', 'Pago', 'Revisión'];

const iconosPago = {
    efectivo: '💰',
    tarjeta_credito: '💳',
    tarjeta_debito: '💳',
    transferencia: '🏦',
    qr: '📱',
};

function avanzar() {
    if (paso.value === 1 && !form.customerName.trim()) {
        toast.error('Ingresá tu nombre para continuar');
        return;
    }
    paso.value++;
}

function retroceder() {
    if (paso.value > 1) {
        paso.value--;
    }
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
        onError: () => toast.error('Hubo un error al procesar tu compra. Intentá de nuevo.'),
    });
}

const total = computed(() => subtotal.value);
</script>

<template>
    <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-slate-800 mb-6">Checkout</h1>

        <!-- Empty cart -->
        <div v-if="items.length === 0" class="text-center py-20">
            <span class="text-6xl block mb-4 opacity-30">🛒</span>
            <p class="text-lg text-slate-400">Tu carrito está vacío.</p>
            <Link href="/tienda/catalogo" class="mt-4 inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 transition-colors">
                Ir al catálogo
            </Link>
        </div>

        <div v-else class="space-y-6">
            <!-- Stepper -->
            <div class="flex items-center justify-center gap-1 sm:gap-2">
                <template v-for="(label, i) in pasos" :key="label">
                    <div class="flex items-center gap-1 sm:gap-2">
                        <div
                            class="flex items-center justify-center w-9 h-9 rounded-full font-bold text-sm transition-all duration-300"
                            :class="paso > i + 1
                                ? 'bg-emerald-600 text-white'
                                : paso === i + 1
                                    ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 scale-110'
                                    : 'bg-slate-200 text-slate-400'"
                        >
                            <span v-if="paso > i + 1">✓</span>
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span
                            class="hidden sm:inline text-xs font-medium transition-colors"
                            :class="paso >= i + 1 ? 'text-slate-700' : 'text-slate-400'"
                        >{{ label }}</span>
                    </div>
                    <div v-if="i < 2" class="flex-1 max-w-[60px] h-0.5 rounded-full transition-colors duration-300" :class="paso > i + 1 ? 'bg-emerald-600' : 'bg-slate-200'"></div>
                </template>
            </div>

            <!-- Step 1: Datos -->
            <div v-if="paso === 1" class="step-fade rounded-xl bg-white p-6 shadow-sm space-y-4">
                <h2 class="font-semibold text-slate-800">Tus datos</h2>
                <label class="block">
                    <span class="text-sm text-slate-600">Nombre del comprador</span>
                    <input
                        v-model="form.customerName"
                        @keyup.enter="avanzar"
                        type="text"
                        placeholder="Ej: Juan Pérez"
                        class="mt-1 w-full rounded-lg border border-slate-300 py-2.5 px-4 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 focus:outline-none transition-colors"
                    />
                </label>
                <button
                    @click="avanzar"
                    :disabled="!form.customerName.trim()"
                    class="w-full rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed transition-colors"
                >
                    Continuar →
                </button>
            </div>

            <!-- Step 2: Pago -->
            <div v-else-if="paso === 2" class="step-fade rounded-xl bg-white p-6 shadow-sm space-y-4">
                <h2 class="font-semibold text-slate-800">Método de pago</h2>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        v-for="m in metodosDePago"
                        :key="m.value"
                        @click="form.paymentMethod = m.value"
                        class="flex items-center gap-3 rounded-xl border-2 py-3.5 px-4 text-sm font-medium transition-all text-left"
                        :class="form.paymentMethod === m.value
                            ? 'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-sm'
                            : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50'"
                    >
                        <span class="text-xl">{{ iconosPago[m.value] ?? '💳' }}</span>
                        {{ etiquetaMetodoPago[m.value] ?? m.label }}
                    </button>
                </div>
                <div class="flex gap-3 pt-2">
                    <button @click="retroceder" class="rounded-lg border border-slate-300 px-5 py-3 font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        ← Volver
                    </button>
                    <button @click="avanzar" class="flex-1 rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700 transition-colors">
                        Continuar →
                    </button>
                </div>
            </div>

            <!-- Step 3: Revisar -->
            <div v-else class="step-fade rounded-xl bg-white p-6 shadow-sm space-y-4">
                <h2 class="font-semibold text-slate-800">Revisá tu pedido</h2>

                <div class="space-y-2">
                    <div v-for="item in items" :key="item.id" class="flex items-center gap-3 text-sm">
                        <span class="text-xl">{{ emojiProducto(item.nombre) }}</span>
                        <span class="flex-1 text-slate-600">{{ item.quantity }} × {{ item.nombre }}</span>
                        <span class="font-medium text-slate-800">{{ formato.dinero(item.precio * item.quantity * 100) }}</span>
                    </div>
                </div>

                <hr class="border-slate-100" />

                <div class="space-y-1 text-sm">
                    <div class="flex justify-between text-slate-500">
                        <span>Comprador</span>
                        <span class="text-slate-700 font-medium">{{ form.customerName }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Pago</span>
                        <span class="text-slate-700 font-medium">{{ iconosPago[form.paymentMethod] }} {{ etiquetaMetodoPago[form.paymentMethod] }}</span>
                    </div>
                </div>

                <hr class="border-slate-100" />

                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span class="text-emerald-600">{{ formato.dinero(total * 100) }} ARS</span>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="retroceder" class="rounded-lg border border-slate-300 px-5 py-3 font-medium text-slate-600 hover:bg-slate-50 transition-colors">
                        ← Volver
                    </button>
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="flex-1 rounded-lg bg-emerald-600 py-3 font-semibold text-white hover:bg-emerald-700 disabled:opacity-60 transition-colors flex items-center justify-center gap-2"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ form.processing ? 'Procesando…' : 'Confirmar compra' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.step-fade {
    animation: stepFadeIn 0.3s ease-out;
}
@keyframes stepFadeIn {
    from { opacity: 0; transform: translateX(12px); }
    to { opacity: 1; transform: translateX(0); }
}
</style>
