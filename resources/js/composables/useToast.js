import { reactive } from 'vue';

/**
 * Composable de notificaciones toast.
 * Singleton — todas las instancias comparten el mismo estado.
 *
 * @example
 * const toast = useToast();
 * toast.success('Producto agregado al carrito');
 */

const toasts = reactive([]);
let nextId = 0;

export function useToast() {
    function show(message, type = 'info', duration = 3000) {
        const id = nextId++;
        toasts.push({ id, message, type });
        setTimeout(() => dismiss(id), duration);
        return id;
    }

    function dismiss(id) {
        const index = toasts.findIndex((t) => t.id === id);
        if (index !== -1) toasts.splice(index, 1);
    }

    return {
        toasts,
        show,
        dismiss,
        success: (msg, d) => show(msg, 'success', d),
        error: (msg, d) => show(msg, 'error', d ?? 4000),
        info: (msg, d) => show(msg, 'info', d),
    };
}
