<?php
declare(strict_types=1);

Final Class Producto {
    private $nombre;
    private $codigo;

    public function __construct(string $nombre, string $codigo)
    {
        $this->validarNombre($nombre);
        $this->validarCodigo($codigo);
        $this->nombre = $nombre;
        $this->codigo = $codigo;
    }

    private function validarNombre(string $nombre)
    {
        if (0 >= strlen(trim($nombre))) {
            throw new Exception();
        }
    }

    private function validarCodigo(string $codigo)
    {
        if (0 >= strlen(trim($codigo))) {
            throw new Exception();
        }
    }
}
?>