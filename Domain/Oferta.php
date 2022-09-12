<?php
class Oferta {
    private $producto;
    private $descuento;

    public function __construct(Producto $producto, float $descuento)
    {
        $this->validarDescuento($descuento);
        $this->producto = $producto;
        $this->descuento = $descuento;
    }

    private function validarDescuento(float $descuento)
    {
        if (0 >= $descuento) {
            throw new Exception();
        }
    }
}
?>
