<?php

declare (strict_types=1);
namespace Doctrine\Common\Collections;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Composite_Expression;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\Expr\Value;
/**
 * Builder for Expressions in the {@link Selectable} interface.
 *
 * Important Notice for interoperable code: You have to use scalar
 * values only for comparisons, otherwise the behavior of the comparison
 * may be different between implementations (Array vs ORM vs ODM).
 */
final class Expression_Builder
{
    public function and_x(Expression ...$expressions): Composite_Expression
    {
        return new Composite_Expression(Composite_Expression::TYPE_AND, $expressions);
    }
    public function or_x(Expression ...$expressions): Composite_Expression
    {
        return new Composite_Expression(Composite_Expression::TYPE_OR, $expressions);
    }
    public function not(Expression $expression): Composite_Expression
    {
        return new Composite_Expression(Composite_Expression::TYPE_NOT, [$expression]);
    }
    public function eq(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::EQ, new Value($value));
    }
    public function gt(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::GT, new Value($value));
    }
    public function lt(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::LT, new Value($value));
    }
    public function gte(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::GTE, new Value($value));
    }
    public function lte(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::LTE, new Value($value));
    }
    public function neq(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::NEQ, new Value($value));
    }
    public function is_null(string $field): Comparison
    {
        return new Comparison($field, Comparison::EQ, new Value(null));
    }
    public function is_not_null(string $field): Comparison
    {
        return new Comparison($field, Comparison::NEQ, new Value(null));
    }
    /** @param mixed[] $values */
    public function in(string $field, array $values): Comparison
    {
        return new Comparison($field, Comparison::IN, new Value($values));
    }
    /** @param mixed[] $values */
    public function not_in(string $field, array $values): Comparison
    {
        return new Comparison($field, Comparison::NIN, new Value($values));
    }
    public function contains(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::CONTAINS, new Value($value));
    }
    public function member_of(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::MEMBER_OF, new Value($value));
    }
    public function starts_with(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::STARTS_WITH, new Value($value));
    }
    public function ends_with(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::ENDS_WITH, new Value($value));
    }
}