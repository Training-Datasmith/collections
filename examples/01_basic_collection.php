<?php

declare(strict_types=1);

/**
 * Example 01 — Basic Array_Collection usage.
 *
 * Demonstrates adding, iterating, filtering, and mapping a typed collection.
 *
 * Run:  php examples/01_basic_collection.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\Common\Collections\Array_Collection;

// --- Build a collection of user names -----------------------------------------
/** @var Array_Collection<int, string> $names */
$names = new Array_Collection(['Alice', 'Bob', 'Charlie', 'Diana']);

echo 'Count: ' . $names->count() . PHP_EOL;           // 4
echo 'First: ' . $names->first() . PHP_EOL;            // Alice
echo 'Last:  ' . $names->last() . PHP_EOL;             // Diana

// --- Filter: keep only names longer than 4 characters -------------------------
$long_names = $names->filter(static fn(string $name): bool => strlen($name) > 4);

echo 'Long names: ' . implode(', ', $long_names->to_array()) . PHP_EOL;
// Charlie, Diana

// --- Map: convert to upper-case -----------------------------------------------
$upper = $names->map(static fn(string $name): string => strtoupper($name));

echo 'Upper: ' . implode(', ', $upper->to_array()) . PHP_EOL;
// ALICE, BOB, CHARLIE, DIANA

// --- Partition: split by first letter > 'B' -----------------------------------
[$after_b, $up_to_b] = $names->partition(
    static fn(int $key, string $name): bool => $name[0] > 'B'
);

echo 'After B: ' . implode(', ', $after_b->to_array()) . PHP_EOL;  // Charlie, Diana
echo 'Up to B: ' . implode(', ', $up_to_b->to_array()) . PHP_EOL;   // Alice, Bob

// --- ArrayAccess interface ----------------------------------------------------
$names[] = 'Eve';                     // append via []
echo 'Added Eve: ' . $names->last() . PHP_EOL;  // Eve

unset($names[1]);                     // remove 'Bob' by index
echo 'After removing index 1: ' . implode(', ', $names->to_array()) . PHP_EOL;
// Alice, Charlie, Diana, Eve
