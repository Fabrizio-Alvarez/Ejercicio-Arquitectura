<?php
declare(strict_types=1);
namespace Domain\Venta;

Final Class Producto {
    private string $id;
    private string $nombre;
    private string $codigo;
    private Precio $precio;

    private function __construct(string $nombre, string $codigo, Precio $precio)
    {
        $this->nombre = $nombre;
        $this->codigo = $codigo;
        $this->precio = $precio;
    }

    public static function CrearProductoParaVenta(string $nombre, string $codigo, Precio $precio)
    {
        return new self($nombre, $codigo, $precio);
    }

    public function getPrecio(): Precio
    {
        return $this->precio;
    }
}
?>
