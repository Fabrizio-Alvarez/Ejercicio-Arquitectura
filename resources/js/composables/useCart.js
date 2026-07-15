import { reactive, computed, watch } from 'vue';

/**
 * Composable del carrito de compras del storefront.
 *
 * Estado reactivo persistido en localStorage.
 * Singleton — todos los componentes comparten la misma instancia.
 */

const STORAGE_KEY = 'supermercado-cart';

function loadCart() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

const items = reactive(loadCart());

watch(items, (val) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(val));
}, { deep: true });

export function useCart() {
    const count = computed(() => items.reduce((sum, i) => sum + i.quantity, 0));

    const subtotal = computed(() =>
        items.reduce((sum, i) => sum + i.precio * i.quantity, 0),
    );

    function add(producto) {
        const existing = items.find((i) => i.id === producto.id);
        if (existing) {
            existing.quantity++;
        } else {
            items.push({
                id: producto.id,
                nombre: producto.nombre,
                precio: producto.precio,
                moneda: producto.moneda ?? 'ARS',
                quantity: 1,
            });
        }
    }

    function remove(id) {
        const index = items.findIndex((i) => i.id === id);
        if (index !== -1) items.splice(index, 1);
    }

    function updateQuantity(id, quantity) {
        const item = items.find((i) => i.id === id);
        if (item) {
            if (quantity <= 0) {
                remove(id);
            } else {
                item.quantity = quantity;
            }
        }
    }

    function increment(id) {
        const item = items.find((i) => i.id === id);
        if (item) item.quantity++;
    }

    function decrement(id) {
        const item = items.find((i) => i.id === id);
        if (item) {
            item.quantity--;
            if (item.quantity <= 0) remove(id);
        }
    }

    function clear() {
        items.splice(0, items.length);
    }

    return { items, count, subtotal, add, remove, updateQuantity, increment, decrement, clear };
}
