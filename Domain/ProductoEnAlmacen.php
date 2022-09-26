<?php
declare(strict_types=1);

Final Class ProductoEnAlmacen {
    private $producto;
    private $precio;

    public function __construct(Producto $producto, Precio $precio)
    {
        $this->producto = $producto;
        $this->precio = $precio;
    }
}
?>
