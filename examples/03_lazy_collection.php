<?php

declare(strict_types=1);

/**
 * Example 03 — Lazy collection with deferred initialisation.
 *
 * Demonstrates extending Abstract_Lazy_Collection to wrap a data source
 * that should only be loaded when first accessed.
 *
 * Run:  php examples/03_lazy_collection.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Collections\Abstract_Lazy_Collection;
use Doctrine\Common\Collections\Array_Collection;

/**
 * A collection that simulates fetching rows from a database on first access.
 *
 * @extends Abstract_Lazy_Collection<int, string>
 */
final class Lazy_Row_Collection extends Abstract_Lazy_Collection
{
    private bool $loaded = false;

    /** @param callable(): list<string> $loader */
    public function __construct(private readonly mixed $loader)
    {
    }

    protected function do_initialize(): void
    {
        $rows = ($this->loader)();
        $this->collection = new Array_Collection($rows);
        $this->loaded = true;
        echo '  [Lazy_Row_Collection] Loaded ' . count($rows) . ' rows from data source.' . PHP_EOL;
    }

    public function is_loaded(): bool
    {
        return $this->loaded;
    }
}

// --- Usage --------------------------------------------------------------------
$collection = new Lazy_Row_Collection(static function (): array {
    // Simulate an expensive DB query
    return ['row_one', 'row_two', 'row_three'];
});

echo 'Collection created. Is loaded: ' . ($collection->is_loaded() ? 'yes' : 'no') . PHP_EOL;
// Collection created. Is loaded: no

echo 'First element: ' . $collection->first() . PHP_EOL;
//   [Lazy_Row_Collection] Loaded 3 rows from data source.
// First element: row_one

echo 'Is loaded after first access: ' . ($collection->is_loaded() ? 'yes' : 'no') . PHP_EOL;
// Is loaded after first access: yes

echo 'Count: ' . $collection->count() . PHP_EOL;  // 3 (no second load)
