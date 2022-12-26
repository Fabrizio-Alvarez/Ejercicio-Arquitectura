<?php
declare(strict_types=1);

Final Class Precio {
    private $monto;
    private $moneda;

    private function __construct(float $monto, string $moneda)
    {
        $this->validarMonto($monto);
        $this->monto = $monto;
        $this->moneda = $moneda;
    }

    public static function CrearPrecioEnPesos($monto)
    {
        return new self($monto, '$AR');
    }

    private function validarMonto(float $monto)
    {
        if (0 >= $monto) {
            throw new Exception();
        }
    }

    public function getMonto():float
    {
        return $this->monto;
    }
}
?>
