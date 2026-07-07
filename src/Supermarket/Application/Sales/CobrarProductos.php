<?php

declare(strict_types=1);

namespace Supermarket\Application\Sales;

use Supermarket\Domain\Catalog\OfferRepository;
use Supermarket\Domain\Catalog\ProductRepository;
use Supermarket\Domain\Sales\Pricer;
use Supermarket\Domain\Sales\Sale;
use Supermarket\Domain\Sales\SaleRepository;

/**
 * Use case: CobrarProductos.
 *
 * A cashier registers the products a customer brings to the checkout. The
 * system prices each one (applying the best active offer), builds a Sale,
 * confirms it, and persists it — returning the confirmed Sale with its total.
 */
final class CobrarProductos
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly OfferRepository $offers,
        private readonly SaleRepository $sales,
        private readonly Pricer $pricer,
    ) {}

    public function execute(CobrarRequest $request): Sale
    {
        $now = new \DateTimeImmutable('now');

        $sale = new Sale($request->saleId, $request->cashierId, $request->customerName, $now);

        foreach ($request->items as $item) {
            $product = $this->products->find($item->productId);

            if ($product === null) {
                throw ProductNotFoundException::forId($item->productId);
            }

            $offers = $this->offers->findByProduct($item->productId);

            $sale->addLine($this->pricer->price($product, $item->quantity, $offers, $now));
        }

        $sale->confirm();

        $this->sales->save($sale);

        return $sale;
    }
}
