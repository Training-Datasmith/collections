# Architecture: doctrine/collections

## Purpose

Doctrine Collections provides a rich, typed collection abstraction on top of PHP arrays. It is used throughout the Doctrine ecosystem (ORM, ODM, Common) wherever a domain-aware, filterable ordered map is needed.

## Directory Structure

```
src/
  Collection.php                  — Mutable ordered-map interface (extends ReadableCollection + ArrayAccess)
  Readable_Collection.php         — Read-only interface (Countable, IteratorAggregate, Selectable)
  Selectable.php                  — Criteria-based filtering interface
  Array_Collection.php            — Primary concrete implementation backed by a PHP array
  Abstract_Lazy_Collection.php    — Base for collections that defer initialisation until first access
  Criteria.php                    — Value object that describes a filter/sort/slice query
  Expression_Builder.php          — Fluent DSL for building Criteria expressions
  Order.php                       — Backed enum: Ascending | Descending
  Expr/
    Expression.php                — Marker interface for expression nodes
    Comparison.php                — Leaf node: field OP value
    Composite_Expression.php      — Branch node: AND | OR | NOT of child expressions
    Value.php                     — Wrapper around a scalar comparison target
    Expression_Visitor.php        — Abstract visitor; walk_* methods for each node type
    Closure_Expression_Visitor.php — Visitor that converts an expression tree into a PHP Closure

tests/
  Array_Collection_Test.php / Collection_Test_Case.php — PHPUnit test suite
```

## Key Design Decisions

1. **Visitor pattern for expression evaluation.** `Expression_Visitor` decouples the expression tree from the evaluation strategy. The same `Criteria` object can be evaluated in-memory (via `Closure_Expression_Visitor`) or translated to SQL/DQL by ORM/ODM visitors.

2. **Template/generic typing via PHPStan.** The interfaces use `@phpstan-template TKey of array-key` / `@phpstan-template T` so static analysis tools treat collections as fully generic without requiring PHP native generics.

3. **`Order` as a backed enum.** `Order::Ascending` / `Order::Descending` replaces string constants, giving exhaustive match safety and IDE completion.

4. **`Array_Collection` as a consistent constructor.** The protected `create_from()` factory method lets subclasses override the concrete type returned by `map()`, `filter()`, `partition()`, and `matching()` without reimplementing each method.

5. **Depth-limited field path traversal.** `Closure_Expression_Visitor::get_object_field_value()` caps dotted-path nesting at 10 levels to prevent stack exhaustion from adversarial input.

## Extension Points

- Implement `Selectable` to expose `matching(Criteria)` on a custom collection (e.g. a database-backed proxy).
- Extend `Expression_Visitor` to translate an expression tree into an ORM `QueryBuilder` constraint.
- Extend `Abstract_Lazy_Collection` to wrap any lazy data source; override `do_initialize()` to populate `$this->collection`.

## Dependency Flow

```
Array_Collection
  └── implements Collection (mutable) + Selectable
        └── Collection extends Readable_Collection + ArrayAccess
              └── Readable_Collection extends Countable, IteratorAggregate, Selectable

Criteria  ──uses──>  Expression_Builder  ──builds──>  Comparison / Composite_Expression
Array_Collection::matching()  ──dispatches──>  Closure_Expression_Visitor (extends Expression_Visitor)
```

## Performance Notes

- `contains()` is O(n) — scans the internal array with `in_array(..., strict: true)`.
- `matching()` with complex expressions applies the closure filter in a single O(n) pass.
- Sorting via `matching()` uses `uasort`, which is O(n log n).
- For large collections backed by a database, prefer a custom `Selectable` implementation that pushes the filter to SQL rather than loading all rows into memory.
