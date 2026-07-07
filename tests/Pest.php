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

function products(): \Supermarket\Domain\Catalog\ProductRepository
{
    return app(\Supermarket\Domain\Catalog\ProductRepository::class);
}
