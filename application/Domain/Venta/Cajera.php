<?php
declare(strict_types=1);
namespace Domain\Venta;

use Gondola\Producto;
use Venta\Producto as VentaProducto;

Final Class CajeraService {
    private $nombre;

    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function realizarVenta(array $productos, float $pago, $eventDispacherVenta(crear bien, es para la creación de productos para venta y descuento de stock en gondola de momento))
    {

    }

    private function crearProductosParaVenta(array $productos)
    {

        // if ($this->validarOfertas($producto)) {
        //     $precio = $this->calcularOfertas($producto);
        // }
        // $productoEnVenta = VentaProducto::CrearProductoParaVenta($producto->getNombre(), $producto->getCodigo(), $precio);
        // $productos[] = $productoEnVenta;
    }

    private function validarPago(Precio $pago, float $totalCompra)
    {
        return $totalCompra <= $pago->getMonto();
    }

    private function calcularVuelto(Precio $pago, float $totalCompra)
    {
        return $pago->getMonto() - $totalCompra;
    }


    private function validarOfertas(Producto $producto): array
    {
        return $producto->getOfertas();
    }

    private function calcularOfertas(Producto $producto)
    {
        $descuento = $this->calcularDescuento($producto);
        $precioCalculado = $producto->getPrecio()->getMonto() - $descuento;
        return $precioCalculado;
    }

    private function calcularDescuento(Producto $producto) {
        $descuento = 0;
        foreach ($producto->getOfertas() as $oferta) {
            if ($oferta->enVigencia()) {
                $descuento += $producto->getPrecio()->getMonto() * ($oferta->getDescuento() / 100);
            }
        } return $descuento;
    }
}
?>
