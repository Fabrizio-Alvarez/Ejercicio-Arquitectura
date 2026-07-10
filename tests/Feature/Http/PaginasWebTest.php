<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sin perfil, la raíz redirige al selector', function () {
    $this->get('/')->assertRedirect('/iniciar');
});

it('renderiza la página de stock vía Inertia (repositor)', function () {
    $this->withSession(['perfil' => 'repositor'])->get('/stock')->assertOk();
});

it('renderiza la página de cobrar vía Inertia (cajero)', function () {
    $this->seed();
    $this->withSession(['perfil' => 'cajero'])->get('/cobrar')->assertOk();
});

it('renderiza la página de movimientos vía Inertia (depositista)', function () {
    $this->seed();
    $this->withSession(['perfil' => 'depositista'])->get('/movimientos')->assertOk();
});
