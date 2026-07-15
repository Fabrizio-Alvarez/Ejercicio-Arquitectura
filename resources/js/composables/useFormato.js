/**
 * Composable de formato de dinero y fechas.
 *
 * Unifica las tres variantes que existían dispersas:
 * - dinero(centavos) → string decimal (Tablero)
 * - dineroVO(obj) → "123.45 ARS" desde VO { amount, currency } (Cierre)
 * - fecha(iso) → fecha localizada (Catalogo, Reportes)
 */

export function useFormato() {
    function dinero(centavos) {
        return (Number(centavos) / 100).toFixed(2);
    }

    function dineroVO(vo) {
        if (!vo) return '—';
        return `${(vo.amount / 100).toFixed(2)} ${vo.currency}`;
    }

    function fecha(iso) {
        return iso ? new Date(iso).toLocaleString('es-AR') : '';
    }

    return { dinero, dineroVO, fecha };
}
