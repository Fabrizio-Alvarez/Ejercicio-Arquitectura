/**
 * Mapas centralizados de etiquetas y colores para tipos, ubicaciones y métodos.
 *
 * Antes duplicados en Tablero.vue, Movimientos.vue, Reportes.vue, Catalogo.vue,
 * Alertas.vue y Auditoria.vue — ahora un solo origen de verdad.
 */

export const etiquetaTipoMovimiento = {
    venta:           { texto: 'Venta',           clase: 'bg-amber-100 text-amber-800' },
    reposicion:      { texto: 'Reposición',      clase: 'bg-emerald-100 text-emerald-800' },
    reabastecimiento:{ texto: 'Reabastecimiento', clase: 'bg-sky-100 text-sky-800' },
    ajuste:          { texto: 'Ajuste',          clase: 'bg-slate-200 text-slate-800' },
    devolucion:      { texto: 'Devolución',      clase: 'bg-purple-100 text-purple-800' },
};

export const colorTipoMovimiento = {
    venta:            'bg-amber-400',
    reposicion:       'bg-emerald-400',
    reabastecimiento: 'bg-sky-400',
    ajuste:           'bg-slate-400',
    devolucion:       'bg-purple-400',
};

export const etiquetaUbicacion = {
    gondola:  { texto: 'Góndola',  clase: 'bg-amber-100 text-amber-700' },
    deposito: { texto: 'Depósito', clase: 'bg-red-100 text-red-700' },
};

export const etiquetaMetodoPago = {
    efectivo:         'Efectivo',
    tarjeta_credito:  'Tarjeta de crédito',
    tarjeta_debito:   'Tarjeta de débito',
    transferencia:    'Transferencia',
    qr:               'QR',
};

export const etiquetaEvento = {
    CompraRealizada:  { texto: 'Compra', clase: 'bg-amber-100 text-amber-700' },
    AlertaDeStock:    { texto: 'Alerta', clase: 'bg-red-100 text-red-700' },
    DevolucionRegistrada: { texto: 'Devolución', clase: 'bg-purple-100 text-purple-700' },
};

export const etiquetaEstado = {
    pendiente:       'Pendiente',
    esperando_pago:  'Esperando pago',
    confirmada:      'Confirmada',
    cancelada:       'Cancelada',
};
