<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Supermercado\Application\Catalogo\ActualizarProducto;
use Supermercado\Application\Catalogo\CrearOferta;
use Supermercado\Application\Catalogo\CrearProducto;
use Supermercado\Application\Catalogo\EliminarProducto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Infrastructure\Persistence\OfertaModel;

/**
 * CRUD del catálogo: productos y ofertas. Solo el depositista gestiona el
 * catálogo (rol:depositista en las rutas). Las mutaciones usan use cases del
 * dominio; la lectura/destrucción de ofertas usa OfertaModel directamente
 * (la Oferta es un value object sin identidad de dominio).
 */
final class CatalogoController extends Controller
{
    /** @return array<int, array{id:string, nombre:string, precio:float, moneda:string}> */
    public function productos(ProductoRepository $repo): JsonResponse
    {
        return response()->json(
            array_map(static fn ($p) => [
                'id' => $p->id(),
                'nombre' => $p->name(),
                'precio' => $p->price()->amount() / 100,
                'moneda' => $p->price()->currency(),
            ], $repo->all())
        );
    }

    public function crearProducto(Request $request, CrearProducto $useCase): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'string'],
            'nombre' => ['required', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string'],
        ]);

        $producto = $useCase->execute(
            $data['id'],
            $data['nombre'],
            (int) round((float) $data['precio'] * 100),
            $data['moneda'],
        );

        return response()->json([
            'id' => $producto->id(),
            'nombre' => $producto->name(),
            'precio' => $producto->price()->amount() / 100,
            'moneda' => $producto->price()->currency(),
        ], 201);
    }

    public function actualizarProducto(Request $request, string $id, ActualizarProducto $useCase): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string'],
            'precio' => ['required', 'numeric', 'min:0'],
            'moneda' => ['required', 'string'],
        ]);

        $useCase->execute(
            $id,
            $data['nombre'],
            (int) round((float) $data['precio'] * 100),
            $data['moneda'],
        );

        return response()->json(['ok' => true]);
    }

    public function eliminarProducto(string $id, EliminarProducto $useCase): JsonResponse
    {
        $useCase->execute($id);

        return response()->json(['ok' => true]);
    }

    public function crearOferta(Request $request, CrearOferta $useCase): JsonResponse
    {
        $data = $request->validate([
            'productoId' => ['required', 'string'],
            'porcentaje' => ['required', 'numeric', 'min:0', 'max:100'],
            'validoDesde' => ['required', 'string'],
            'validoHasta' => ['required', 'string'],
        ]);

        $useCase->execute(
            $data['productoId'],
            (float) $data['porcentaje'],
            $data['validoDesde'],
            $data['validoHasta'],
        );

        return response()->json(['ok' => true], 201);
    }

    public function eliminarOferta(int $id): JsonResponse
    {
        OfertaModel::destroy($id);

        return response()->json(['ok' => true]);
    }
}
