<?php
declare(strict_types=1);
namespace Domain\Gondola;

use DateTime;
use Exception;

Final Class Oferta {
    private int $id;
    private string $codigo;
    private float $descuento;
    private DateTime $inicioVigencia;
    private DateTime $finVigencia;


    public function __construct(string $codigo, float $descuento, Date $inicioVigencia, Date $finVigencia)
    {
        $this->validarDescuento($descuento);
        $this->codigo = $codigo;
        $this->descuento = $descuento;
        $this->inicioVigencia = $inicioVigencia;
        $this->finVigencia = $finVigencia;
    }

    private function validarDescuento(float $descuento)
    {
        if (0 >= $descuento) {
            throw new Exception();
        }
    }

    public function getDescuento(): float
    {
        return $this->descuento;
    }

    public function enVigencia($fecha) {
        return $this->inicioVigencia <= $fecha && $fecha <= $this->finVigencia;
    }
}
?>
