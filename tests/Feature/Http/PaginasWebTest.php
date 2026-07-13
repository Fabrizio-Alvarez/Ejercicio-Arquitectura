<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renderiza la página de stock vía Inertia (repositor)', function () {
    $this->actingAs(repositor())->get('/stock')->assertOk();
});

it('renderiza la página de cobrar vía Inertia (cajero)', function () {
    $this->seed();
    $this->actingAs(cajero())->get('/cobrar')->assertOk();
});

it('renderiza la página de movimientos vía Inertia (depositista)', function () {
    $this->seed();
    $this->actingAs(depositista())->get('/movimientos')->assertOk();
});

it('renderiza la página de alertas vía Inertia (depositista)', function () {
    $this->actingAs(depositista())->get('/alertas')->assertOk();
});

it('renderiza la página de cierre de caja vía Inertia (cajero)', function () {
    $this->actingAs(cajero())->get('/cierre')->assertOk();
});
