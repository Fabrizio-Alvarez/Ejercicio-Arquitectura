<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('emite un token de API con credenciales válidas vía POST /api/tokens', function () {
    User::create(['name' => 'Cajero', 'email' => 'c@test', 'password' => 'secret', 'rol' => 'cajero']);

    $this->postJson('/api/tokens', ['email' => 'c@test', 'password' => 'secret'])
        ->assertOk()
        ->assertJsonPath('rol', 'cajero')
        ->assertJsonStructure(['token', 'rol']);
});

it('rechaza credenciales inválidas en /api/tokens (422)', function () {
    User::create(['name' => 'Cajero', 'email' => 'c@test', 'password' => 'secret', 'rol' => 'cajero']);

    $this->postJson('/api/tokens', ['email' => 'c@test', 'password' => 'wrong'])
        ->assertStatus(422);
});

it('rechaza peticiones API sin autenticación (401)', function () {
    $this->getJson('/api/stock')->assertStatus(401);
    $this->postJson('/api/checkout', [])->assertStatus(401);
});

it('permite al repositor ver stock con token Bearer', function () {
    $token = repositor()->createToken('api')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/stock')
        ->assertOk();
});

it('rechaza al cajero ver stock (403 — rol incorrecto)', function () {
    $token = cajero()->createToken('api')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/stock')
        ->assertStatus(403);
});

it('rechaza al repositor hacer checkout (403 — solo cajero)', function () {
    $token = repositor()->createToken('api')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/checkout', [
            'saleId' => 's-1', 'cashierId' => 'c-1', 'customerName' => 'Jane',
            'paymentMethod' => 'efectivo',
            'items' => [['productId' => 'p-1', 'quantity' => 1]],
        ])
        ->assertStatus(403);
});

it('el depositista puede reabastecer pero no cobrar', function () {
    $token = depositista()->createToken('api')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/checkout', [
            'saleId' => 's-1', 'cashierId' => 'c-1', 'customerName' => 'Jane',
            'paymentMethod' => 'efectivo',
            'items' => [['productId' => 'p-1', 'quantity' => 1]],
        ])
        ->assertStatus(403);
});
