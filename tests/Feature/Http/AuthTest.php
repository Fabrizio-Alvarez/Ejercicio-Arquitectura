<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('autentica con credenciales válidas y redirige al home del rol', function () {
    User::create(['name' => 'Cajero', 'email' => 'c@test', 'password' => 'secret', 'rol' => 'cajero']);

    $this->post('/login', ['email' => 'c@test', 'password' => 'secret'])->assertRedirect('/cobrar');
});

it('el depositista autenticado va a /movimientos', function () {
    User::create(['name' => 'Depo', 'email' => 'd@test', 'password' => 'secret', 'rol' => 'depositista']);

    $this->post('/login', ['email' => 'd@test', 'password' => 'secret'])->assertRedirect('/movimientos');
});

it('rechaza credenciales inválidas con error de email', function () {
    User::create(['name' => 'Cajero', 'email' => 'c@test', 'password' => 'secret', 'rol' => 'cajero']);

    $this->post('/login', ['email' => 'c@test', 'password' => 'wrong'])->assertSessionHasErrors(['email']);
});

it('rechaza un email que no existe', function () {
    $this->post('/login', ['email' => 'nadie@test', 'password' => 'secret'])->assertSessionHasErrors(['email']);
});

it('requiere email y password', function () {
    $this->post('/login', [])->assertSessionHasErrors(['email', 'password']);
});

it('redirige si se accede al login estando autenticado', function () {
    $this->actingAs(cajero())->get('/login')->assertRedirect();
});
