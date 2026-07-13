<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The TestCase is bound for Feature tests so the Laravel application boots
| (providers register, container bindings resolve) before each test.
|
*/

uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function products(): \Supermercado\Domain\Catalogo\ProductoRepository
{
    return app(\Supermercado\Domain\Catalogo\ProductoRepository::class);
}

function cajero(): \App\Models\User
{
    return \App\Models\User::firstOrCreate(
        ['email' => 'cajero@test'],
        ['name' => 'Cajero', 'password' => 'secret', 'rol' => 'cajero'],
    );
}

function depositista(): \App\Models\User
{
    return \App\Models\User::firstOrCreate(
        ['email' => 'depo@test'],
        ['name' => 'Depo', 'password' => 'secret', 'rol' => 'depositista'],
    );
}

function repositor(): \App\Models\User
{
    return \App\Models\User::firstOrCreate(
        ['email' => 'repo@test'],
        ['name' => 'Repo', 'password' => 'secret', 'rol' => 'repositor'],
    );
}
