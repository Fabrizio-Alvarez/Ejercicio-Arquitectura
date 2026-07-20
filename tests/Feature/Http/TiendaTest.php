<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Supermercado\Domain\Catalogo\Producto;
use Supermercado\Domain\Catalogo\ProductoRepository;
use Supermercado\Domain\Comun\Dinero;
use Supermercado\Domain\Stock\Deposito;
use Supermercado\Domain\Stock\DepositoRepository;
use Supermercado\Domain\Stock\Gondola;
use Supermercado\Domain\Stock\GondolaRepository;
use Supermercado\Domain\Ventas\EstadoDeVenta;
use Supermercado\Domain\Ventas\LineaDeVenta;
use Supermercado\Domain\Ventas\MetodoDePago;
use Supermercado\Domain\Ventas\Venta;
use Supermercado\Domain\Ventas\VentaRepository;

uses(RefreshDatabase::class);

// ────────────────────────────────────────────────────────────────────────────
//  Helpers
// ────────────────────────────────────────────────────────────────────────────

function seedProductos(): void
{
    $productos = app(ProductoRepository::class);
    $gondolas = app(GondolaRepository::class);
    $depositos = app(DepositoRepository::class);

    $datos = [
        ['p-1', 'Leche 1L', 150, 45, 500],
        ['p-2', 'Pan', 300, 8, 160],
        ['p-3', 'Café 500g', 2500, 60, 800],
    ];

    foreach ($datos as [$id, $nombre, $precio, $gondola, $deposito]) {
        $productos->save(new Producto($id, $nombre, new Dinero($precio, 'ARS')));
        $gondolas->save(new Gondola($id, $gondola));
        $depositos->save(new Deposito($id, $deposito));
    }
}

function item(string $id, int $qty, string $nombre = 'Test', float $precio = 1.50): array
{
    return [
        'productId' => $id,
        'quantity' => $qty,
        'productName' => $nombre,
        'unitPrice' => $precio,
    ];
}

// ────────────────────────────────────────────────────────────────────────────
//  Storefront público — catálogo y producto
// ────────────────────────────────────────────────────────────────────────────

it('renderiza la página de inicio de la tienda', function () {
    seedProductos();
    $this->get('/tienda')->assertOk();
});

it('renderiza el catálogo con productos', function () {
    seedProductos();
    $response = $this->get('/tienda/catalogo');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('productos', 3));
});

it('renderiza el detalle de un producto existente', function () {
    seedProductos();
    $this->get('/tienda/producto/p-1')->assertOk();
});

it('devuelve 404 para un producto inexistente', function () {
    $this->get('/tienda/producto/no-existe')->assertNotFound();
});

it('filtra el catálogo por término de búsqueda', function () {
    seedProductos();
    $response = $this->get('/tienda/catalogo?q=leche');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('productos', 1));
});

it('ordena el catálogo por precio ascendente', function () {
    seedProductos();
    $response = $this->get('/tienda/catalogo?sort=precio-asc');

    $response->assertOk();
});

// ────────────────────────────────────────────────────────────────────────────
//  Checkout
// ────────────────────────────────────────────────────────────────────────────

it('renderiza la página de checkout', function () {
    $this->get('/tienda/checkout')->assertOk();
});

it('procesa un checkout exitoso y redirige a confirmación', function () {
    seedProductos();

    $response = $this->withSession([])->post('/tienda/checkout', [
        'customerName' => 'Cliente Test',
        'paymentMethod' => 'efectivo',
        'items' => [
            item('p-1', 2, 'Leche 1L', 1.50),
        ],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('venta');
});

it('rechaza checkout sin items', function () {
    $this->post('/tienda/checkout', [
        'customerName' => 'Cliente Test',
        'paymentMethod' => 'efectivo',
        'items' => [],
    ])->assertSessionHasErrors('items');
});

it('rechaza checkout con método de pago inválido', function () {
    seedProductos();

    $this->post('/tienda/checkout', [
        'customerName' => 'Cliente Test',
        'paymentMethod' => 'bitcoin',
        'items' => [item('p-1', 1)],
    ])->assertSessionHasErrors('paymentMethod');
});

// ────────────────────────────────────────────────────────────────────────────
//  Auth de cliente — registro y login
// ────────────────────────────────────────────────────────────────────────────

it('renderiza la página de registro', function () {
    $this->get('/tienda/registro')->assertOk();
});

it('renderiza la página de login de cliente', function () {
    $this->get('/tienda/login')->assertOk();
});

it('registra un nuevo cliente y lo autentica', function () {
    $response = $this->post('/tienda/registro', [
        'name' => 'Nuevo Cliente',
        'email' => 'nuevo@test.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/tienda/catalogo');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'nuevo@test.com',
        'rol' => 'cliente',
    ]);
});

it('rechaza registro con email duplicado', function () {
    cliente(); // crea cliente@test

    $this->post('/tienda/registro', [
        'name' => 'Otro',
        'email' => 'cliente@test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});

it('autentica un cliente válido', function () {
    cliente();

    $response = $this->post('/tienda/login', [
        'email' => 'cliente@test',
        'password' => 'secret',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated('web');
    expect(Auth::user()->rol)->toBe('cliente');
});

it('rechaza login de personal del supermercado', function () {
    cajero();

    $response = $this->post('/tienda/login', [
        'email' => 'cajero@test',
        'password' => 'secret',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('cierra la sesión del cliente', function () {
    $this->actingAs(cliente());

    $this->post('/tienda/logout')->assertRedirect('/tienda');
    $this->assertGuest();
});

// ────────────────────────────────────────────────────────────────────────────
//  Cuenta de cliente — pedidos (requiere auth con rol cliente)
// ────────────────────────────────────────────────────────────────────────────

it('redirige a login si no hay cliente autenticado al ver pedidos', function () {
    $this->get('/tienda/cuenta/pedidos')->assertRedirect('/tienda/login');
});

it('muestra el historial de pedidos del cliente', function () {
    $user = cliente();

    // Crear una venta asociada al cliente
    $ventas = app(VentaRepository::class);
    $ventas->save(Venta::reconstitute(
        id: 'test-venta-1',
        cashierId: 'cliente:' . $user->id,
        customerName: 'Cliente Test',
        metodoDePago: MetodoDePago::Efectivo,
        createdAt: new \DateTimeImmutable(),
        status: EstadoDeVenta::Confirmada,
        lines: [new LineaDeVenta('p-1', 'Leche 1L', 2, new Dinero(150, 'ARS'))],
    ));

    $response = $this->actingAs($user)->get('/tienda/cuenta/pedidos');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('pedidos', 1));
});

it('muestra el detalle de un pedido propio', function () {
    $user = cliente();

    $ventas = app(VentaRepository::class);
    $ventas->save(Venta::reconstitute(
        id: 'test-venta-2',
        cashierId: 'cliente:' . $user->id,
        customerName: 'Cliente Test',
        metodoDePago: MetodoDePago::Efectivo,
        createdAt: new \DateTimeImmutable(),
        status: EstadoDeVenta::Confirmada,
        lines: [new LineaDeVenta('p-1', 'Leche 1L', 2, new Dinero(150, 'ARS'))],
    ));

    $this->actingAs($user)->get('/tienda/cuenta/pedidos/test-venta-2')->assertOk();
});

it('redirige si el cliente intenta ver un pedido ajeno', function () {
    $user = cliente();

    // Venta de OTRO cliente (user ID diferente)
    $ventas = app(VentaRepository::class);
    $ventas->save(Venta::reconstitute(
        id: 'test-venta-ajena',
        cashierId: 'cliente:9999',
        customerName: 'Otro Cliente',
        metodoDePago: MetodoDePago::Efectivo,
        createdAt: new \DateTimeImmutable(),
        status: EstadoDeVenta::Confirmada,
        lines: [new LineaDeVenta('p-1', 'Leche 1L', 1, new Dinero(150, 'ARS'))],
    ));

    $this->actingAs($user)
        ->get('/tienda/cuenta/pedidos/test-venta-ajena')
        ->assertRedirect('/tienda/cuenta/pedidos');
});
