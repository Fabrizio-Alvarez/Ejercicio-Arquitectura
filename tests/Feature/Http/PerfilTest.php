<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sin perfil, toda ruta protegida cae al selector', function () {
    $this->get('/')->assertRedirect('/iniciar');
    $this->get('/stock')->assertRedirect('/iniciar');
    $this->get('/cobrar')->assertRedirect('/iniciar');
    $this->get('/movimientos')->assertRedirect('/iniciar');
});

it('el selector se renderiza sin perfil', function () {
    $this->get('/iniciar')->assertOk();
});

it('al elegir cajero redirige a /cobrar', function () {
    $this->post('/iniciar', ['perfil' => 'cajero'])->assertRedirect('/cobrar');
});

it('al elegir depositista redirige a /movimientos', function () {
    $this->post('/iniciar', ['perfil' => 'depositista'])->assertRedirect('/movimientos');
});

it('al elegir repositor redirige a /stock', function () {
    $this->post('/iniciar', ['perfil' => 'repositor'])->assertRedirect('/stock');
});

it('rechaza un perfil inválido', function () {
    $this->post('/iniciar', ['perfil' => 'gerente'])->assertSessionHasErrors(['perfil']);
});

it('el cajero no puede ver /movimientos (vuelve a su home)', function () {
    $this->withSession(['perfil' => 'cajero'])->get('/movimientos')->assertRedirect('/cobrar');
});

it('el repositor sí accede a /stock', function () {
    $this->withSession(['perfil' => 'repositor'])->get('/stock')->assertOk();
});

it('la raíz envía a cada perfil a su propia home', function () {
    $this->withSession(['perfil' => 'depositista'])->get('/')->assertRedirect('/movimientos');
    $this->withSession(['perfil' => 'repositor'])->get('/')->assertRedirect('/stock');
});

it('/salir limpia el perfil y vuelve al selector', function () {
    $this->withSession(['perfil' => 'cajero'])->post('/salir')->assertRedirect('/iniciar');
    $this->get('/cobrar')->assertRedirect('/iniciar');
});
