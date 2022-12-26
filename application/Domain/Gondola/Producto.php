<?php
declare(strict_types=1);
namespace Domain\Gondola;

Final Class Producto {
    private string $id;
    private string $nombre;
    private string $codigo;
    private array $ofertas = [];
    private Precio $precio;
    private int $stock;
    private Gondola $gondola;
    private bool $enVenta;

    public function __construct(string $nombre, string $codigo, array $ofertas, Precio $precio, int $stock, Gondola $gondola)
    {
        $this->nombre = $nombre;
        $this->codigo = $codigo;
        $this->ofertas = $ofertas;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->gondola = $gondola;
    }

    public function actualizarStock(int $stock)
    {
        $this->stock = $stock;
    }

    private function actualizarPrecio(float $monto)
    {
        $this->precio = Precio::CrearPrecioEnPesos($monto);
    }

    public function agregarOferta(Oferta $oferta): void {
        $this->ofertas[] = $oferta;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getPrecio(): Precio
    {
        return $this->precio;
    }

    public function getOfertas(): array
    {
        return $this->ofertas;
    }
}
?>
