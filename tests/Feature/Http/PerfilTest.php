<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sin sesión, toda ruta protegida cae al login', function () {
    $this->get('/')->assertRedirect('/login');
    $this->get('/stock')->assertRedirect('/login');
    $this->get('/cobrar')->assertRedirect('/login');
    $this->get('/movimientos')->assertRedirect('/login');
    $this->get('/alertas')->assertRedirect('/login');
    $this->get('/cierre')->assertRedirect('/login');
});

it('el login se renderiza sin sesión', function () {
    $this->get('/login')->assertOk();
});

it('el cajero accede a /cobrar', function () {
    $this->actingAs(cajero())->get('/cobrar')->assertOk();
});

it('el cajero accede a /cierre', function () {
    $this->actingAs(cajero())->get('/cierre')->assertOk();
});

it('el cajero no puede ver /movimientos (vuelve al tablero)', function () {
    $this->actingAs(cajero())->get('/movimientos')->assertRedirect('/tablero');
});

it('el cajero no puede ver /alertas (vuelve al tablero)', function () {
    $this->actingAs(cajero())->get('/alertas')->assertRedirect('/tablero');
});

it('el depositista accede a /movimientos y /alertas', function () {
    $this->actingAs(depositista())->get('/movimientos')->assertOk();
    $this->actingAs(depositista())->get('/alertas')->assertOk();
});

it('el repositor accede a /stock', function () {
    $this->actingAs(repositor())->get('/stock')->assertOk();
});

it('el depositista no puede ver /cierre (vuelve al tablero)', function () {
    $this->actingAs(depositista())->get('/cierre')->assertRedirect('/tablero');
});

it('la raíz envía a cada perfil al tablero', function () {
    $this->actingAs(depositista())->get('/')->assertRedirect('/tablero');
    $this->actingAs(repositor())->get('/')->assertRedirect('/tablero');
});

it('/logout cierra la sesión y vuelve al login', function () {
    $this->actingAs(cajero())->post('/logout')->assertRedirect('/login');
    $this->get('/cobrar')->assertRedirect('/login');
});
