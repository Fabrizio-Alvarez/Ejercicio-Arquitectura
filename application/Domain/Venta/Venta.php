<?php
declare(strict_types=1);
namespace Domain\Venta;

use Exception;

Final Class Venta {
    private $cajera;
    private $caja;
    private $pago;
    private $productos;

    private function __construct(Cajera $cajera, Caja $caja, Precio $pago, Producto...$productos)
    {
        $this->cajera = $cajera;
        $this->caja = $caja;
        $this->pago = $pago;
        $this->productos = $productos;
    }

    private function validarPago(Precio $pago, float $totalCompra)
    {
        if (!$this->cajera->validarPago($pago, $totalCompra)) {
            throw new Exception();
        }
    }

    private function calcularVuelto() {
        return $this->cajera->calcularVuelto();
    }



}
?>
