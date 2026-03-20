<?php

declare (strict_types=1);
namespace Doctrine\Common\Collections;

use function array_all;
use function array_any;
use function array_filter;
use const ARRAY_FILTER_USE_BOTH;
use function array_find;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_reduce;
use function array_reverse;
use function array_search;
use function array_slice;
use function array_values;
use ArrayIterator;
use Closure;
use function count;
use function current;
use Doctrine\Common\Collections\Expr\Closure_Expression_Visitor;
use function end;
use function in_array;
use function key;
use function next;
use Override;
use function reset;
use function spl_object_hash;
use Stringable;
use Traversable;
use function uasort;
/**
 * An ArrayCollection is a Collection implementation that wraps a regular PHP array.
 *
 * Warning: Using (un-)serialize() on a collection is not a supported use-case
 * and may break when we change the internals in the future. If you need to
 * serialize a collection use {@link toArray()} and reconstruct the collection
 * manually.
 *
 * @phpstan-template TKey of array-key
 * @phpstan-template T
 * @template-implements Collection<TKey,T>
 * @template-implements Selectable<TKey,T>
 * @phpstan-consistent-constructor
 */
class Array_Collection implements Collection, Selectable, Stringable
{
    /**
     * Initializes a new ArrayCollection.
     *
     * @phpstan-param array<TKey,T> $elements
     */
    public function __construct(
        /**
         * An array containing the entries of this collection.
         *
         * @phpstan-var array<TKey,T>
         * @var mixed[]
         */
        private array $elements = []
    )
    {
    }
    #[Override]
    public function to_array(): array
    {
        return $this->elements;
    }
    #[Override]
    public function first(): mixed
    {
        return reset($this->elements);
    }
    /**
     * Creates a new instance from the specified elements.
     *
     * This method is provided for derived classes to specify how a new
     * instance should be created when constructor semantics have changed.
     *
     * @param array $elements Elements.
     * @phpstan-param array<K,V> $elements
     *
     * @phpstan-return static<K,V>
     *
     * @phpstan-template K of array-key
     * @phpstan-template V
     */
    protected function create_from(array $elements): static
    {
        return new static($elements);
    }
    #[Override]
    public function last(): mixed
    {
        return end($this->elements);
    }
    #[Override]
    public function key(): int|string|null
    {
        return key($this->elements);
    }
    #[Override]
    public function next(): mixed
    {
        return next($this->elements);
    }
    #[Override]
    public function current(): mixed
    {
        return current($this->elements);
    }
    #[Override]
    public function remove(string|int $key): mixed
    {
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            return null;
        }
        $removed = $this->elements[$key];
        unset($this->elements[$key]);
        return $removed;
    }
    #[Override]
    public function remove_element(mixed $element): bool
    {
        $key = array_search($element, $this->elements, true);
        if ($key === false) {
            return false;
        }
        unset($this->elements[$key]);
        return true;
    }
    /**
     * Required by interface ArrayAccess.
     *
     * @param TKey $offset
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        return $this->contains_key($offset);
    }
    /**
     * Required by interface ArrayAccess.
     *
     * @param TKey $offset
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }
    /**
     * Required by interface ArrayAccess.
     *
     * @param TKey|null $offset
     * @param T         $value
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->add($value);
            return;
        }
        /** @phpstan-var TKey $offset */
        $this->set($offset, $value);
    }
    /**
     * Required by interface ArrayAccess.
     *
     * @param TKey $offset
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }
    #[Override]
    public function contains_key(string|int $key): bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }
    #[Override]
    public function contains(mixed $element): bool
    {
        return in_array($element, $this->elements, true);
    }
    #[Override]
    public function exists(Closure $p): bool
    {
        return array_any($this->elements, static fn(mixed $element, mixed $key): bool => (bool) $p($key, $element));
    }
    /**
     * @phpstan-param TMaybeContained $element
     *
     * @phpstan-return (TMaybeContained is T ? TKey|false : false)
     *
     * @template TMaybeContained
     */
    #[Override]
    public function index_of(mixed $element): int|string|false
    {
        return array_search($element, $this->elements, true);
    }
    #[Override]
    public function get(string|int $key): mixed
    {
        return $this->elements[$key] ?? null;
    }
    #[Override]
    public function get_keys(): array
    {
        return array_keys($this->elements);
    }
    #[Override]
    public function get_values(): array
    {
        return array_values($this->elements);
    }
    /** @return int<0, max> */
    #[Override]
    public function count(): int
    {
        return count($this->elements);
    }
    #[Override]
    public function set(string|int $key, mixed $value): void
    {
        $this->elements[$key] = $value;
    }
    /**
     * This breaks assumptions about the template type, but it would
     * be a backwards-incompatible change to remove this method
     */
    #[Override]
    public function add(mixed $element): void
    {
        $this->elements[] = $element;
    }
    #[Override]
    public function is_empty(): bool
    {
        return empty($this->elements);
    }
    /**
     * @return Traversable<int|string, mixed>
     * @phpstan-return Traversable<TKey, T>
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }
    /**
     * @phpstan-param Closure(T):U $func
     *
     * @return static
     * @phpstan-return static<TKey, U>
     *
     * @phpstan-template U
     */
    #[Override]
    public function map(Closure $func): Collection
    {
        return $this->create_from(array_map($func, $this->elements));
    }
    #[Override]
    public function reduce(Closure $func, mixed $initial = null): mixed
    {
        return array_reduce($this->elements, $func, $initial);
    }
    /**
     * @phpstan-param Closure(T, TKey):bool $p
     *
     * @return static
     * @phpstan-return static<TKey,T>
     */
    #[Override]
    public function filter(Closure $p): Collection
    {
        return $this->create_from(array_filter($this->elements, $p, ARRAY_FILTER_USE_BOTH));
    }
    #[Override]
    public function find_first(Closure $p): mixed
    {
        return array_find($this->elements, static fn(mixed $element, mixed $key): bool => (bool) $p($key, $element));
    }
    #[Override]
    public function for_all(Closure $p): bool
    {
        return array_all($this->elements, static fn(mixed $element, mixed $key): bool => (bool) $p($key, $element));
    }
    #[Override]
    public function partition(Closure $p): array
    {
        $matches = $no_matches = [];
        foreach ($this->elements as $key => $element) {
            if ($p($key, $element)) {
                $matches[$key] = $element;
            } else {
                $no_matches[$key] = $element;
            }
        }
        return [$this->create_from($matches), $this->create_from($no_matches)];
    }
    /**
     * Returns a string representation of this object.
     */
    #[Override]
    public function __toString(): string
    {
        return self::class . '@' . spl_object_hash($this);
    }
    #[Override]
    public function clear(): void
    {
        $this->elements = [];
    }
    #[Override]
    public function slice(int $offset, int|null $length = null): array
    {
        return array_slice($this->elements, $offset, $length, true);
    }
    /** @phpstan-return Collection<TKey, T>&Selectable<TKey,T> */
    #[Override]
    public function matching(Criteria $criteria): Collection
    {
        $expr = $criteria->get_where_expression();
        $filtered = $this->elements;
        if ($expr) {
            $visitor = new Closure_Expression_Visitor();
            $filter = $visitor->dispatch($expr);
            $filtered = array_filter($filtered, $filter);
        }
        $orderings = $criteria->orderings();
        if ($orderings) {
            $next = null;
            foreach (array_reverse($orderings) as $field => $ordering) {
                $next = Closure_Expression_Visitor::sort_by_field($field, $ordering === Order::Descending ? -1 : 1, $next);
            }
            uasort($filtered, $next);
        }
        $offset = $criteria->get_first_result();
        $length = $criteria->get_max_results();
        if ($offset !== null && $offset > 0 || $length !== null && $length > 0) {
            $filtered = array_slice($filtered, (int) $offset, $length, true);
        }
        return $this->create_from($filtered);
    }
}