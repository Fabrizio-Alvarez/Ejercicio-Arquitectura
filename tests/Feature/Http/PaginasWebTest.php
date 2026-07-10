<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirige la raíz al stock', function () {
    $this->get('/')->assertRedirect('/stock');
});

it('renderiza la página de stock vía Inertia', function () {
    $this->get('/stock')->assertOk();
});

it('renderiza la página de cobrar vía Inertia', function () {
    $this->seed();
    $this->get('/cobrar')->assertOk();
});

it('renderiza la página de movimientos vía Inertia', function () {
    $this->get('/movimientos')->assertOk();
});
