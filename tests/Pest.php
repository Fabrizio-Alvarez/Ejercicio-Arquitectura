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
