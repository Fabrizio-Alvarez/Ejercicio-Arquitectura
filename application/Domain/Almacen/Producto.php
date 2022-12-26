<?php
declare(strict_types=1);
namespace Domain\Almacen;

use Exception;

Final Class Producto {
    private string $id;
    private string $nombre;
    private string $codigo;
    private int $stock;

    public function __construct(string $nombre, string $codigo, int $stock)
    {
        $this->nombre = $nombre;
        $this->codigo = $codigo;
        $this->stock = $stock;
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
