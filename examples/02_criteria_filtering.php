<?php

declare(strict_types=1);

/**
 * Example 02 — Filtering a collection with Criteria.
 *
 * Shows how to build a Criteria with expressions, ordering, and pagination,
 * then apply it to an Array_Collection of domain objects.
 *
 * Run:  php examples/02_criteria_filtering.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Collections\Array_Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;

// --- A simple value object ----------------------------------------------------
final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly string $category,
        public readonly float  $price,
    ) {}
}

// --- Seed data ----------------------------------------------------------------
/** @var Array_Collection<int, Product> $products */
$products = new Array_Collection([
    new Product('Laptop',    'Electronics', 1200.00),
    new Product('Phone',     'Electronics',  699.00),
    new Product('Desk',      'Furniture',    450.00),
    new Product('Chair',     'Furniture',    299.00),
    new Product('Headphones','Electronics',  149.00),
    new Product('Monitor',   'Electronics',  399.00),
]);

// --- Criteria: Electronics under $800, sorted by price ascending, first 3 ----
$criteria = Criteria::create()
    ->where(Criteria::expr()->eq('category', 'Electronics'))
    ->and_where(Criteria::expr()->lt('price', 800.00))
    ->order_by(['price' => Order::Ascending])
    ->set_max_results(3);

$filtered = $products->matching($criteria);

echo 'Electronics under $800 (up to 3, cheapest first):' . PHP_EOL;
foreach ($filtered as $product) {
    echo sprintf('  %-15s $%.2f%s', $product->name, $product->price, PHP_EOL);
}
// Headphones  $149.00
// Monitor     $399.00
// Phone       $699.00

// --- Criteria with OR expression ----------------------------------------------
$expensive_or_furniture = Criteria::create()
    ->where(
        Criteria::expr()->or_x(
            Criteria::expr()->gte('price', 1000.00),
            Criteria::expr()->eq('category', 'Furniture')
        )
    );

$result = $products->matching($expensive_or_furniture);

echo PHP_EOL . 'Expensive (>=1000) or Furniture:' . PHP_EOL;
foreach ($result as $product) {
    echo sprintf('  %-15s %-12s $%.2f%s', $product->name, $product->category, $product->price, PHP_EOL);
}
