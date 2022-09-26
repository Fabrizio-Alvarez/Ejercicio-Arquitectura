<?php
declare(strict_types=1);

Final Class Cajera {
    private $producto;
    private $precio;
    private $stock;
    private $gondola;

    public function __construct(Producto $producto, Precio $precio, int $stock)
    {
        $this->producto = $producto;
        $this->precio = $precio;
        $this->stock = $stock;
    }

    public function actualizarStock(int $stock)
    {
        $this->stock = $stock;
    }

    private function actualizarPrecio(float $monto)
    {
        $this->precio = Precio::CrearPrecioEnPesos($monto);
    }
}
?>
