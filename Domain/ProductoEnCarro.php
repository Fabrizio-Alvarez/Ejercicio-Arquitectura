<?php
declare(strict_types=1);

Final Class ProductoEnCarro {
    private $producto;
    private $precio;
    private $cantidad;

    private function __construct(Producto $producto, Precio $precio, int $cantidad)
    {
        $this->producto = $producto;
        $this->precio = $precio;
        $this->cantidad = $cantidad;
    }

    public function CrearProductoEnCarro($producto, $precio, $cantidad)
    {
        $this->validarCantidad($cantidad);
        return new self($producto, $precio, $cantidad);
    }

    private function validarCantidad(int $cantidad)
    {
        if (0 >= $cantidad) {
            throw new Exception();
        }
    }
}
?>
