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
 *
 * @since 1.0
 */
final class Expression_Builder
{
    /**
     * Creates a composite AND expression from two or more child expressions.
     *
     * All child expressions must evaluate to true for the composite to pass.
     *
     * @param Expression ...$expressions Two or more expressions to combine.
     * @return Composite_Expression A composite AND node.
     * @since 1.0
     */
    public function and_x(Expression ...$expressions): Composite_Expression
    {
        return new Composite_Expression(Composite_Expression::TYPE_AND, $expressions);
    }

    /**
     * Creates a composite OR expression from two or more child expressions.
     *
     * At least one child expression must evaluate to true for the composite to pass.
     *
     * @param Expression ...$expressions Two or more expressions to combine.
     * @return Composite_Expression A composite OR node.
     * @since 1.0
     */
    public function or_x(Expression ...$expressions): Composite_Expression
    {
        return new Composite_Expression(Composite_Expression::TYPE_OR, $expressions);
    }

    /**
     * Negates a single expression.
     *
     * @param Expression $expression The expression to negate.
     * @return Composite_Expression A composite NOT node wrapping $expression.
     * @since 2.1
     */
    public function not(Expression $expression): Composite_Expression
    {
        return new Composite_Expression(Composite_Expression::TYPE_NOT, [$expression]);
    }

    /**
     * Creates an equality comparison: field == value.
     *
     * @param string $field The field/property name to compare.
     * @param mixed  $value The scalar value to compare against.
     * @return Comparison
     * @since 1.0
     */
    public function eq(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::EQ, new Value($value));
    }

    /**
     * Creates a greater-than comparison: field > value.
     *
     * @param string $field The field/property name to compare.
     * @param mixed  $value The scalar threshold value.
     * @return Comparison
     * @since 1.0
     */
    public function gt(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::GT, new Value($value));
    }

    /**
     * Creates a less-than comparison: field < value.
     *
     * @param string $field The field/property name to compare.
     * @param mixed  $value The scalar threshold value.
     * @return Comparison
     * @since 1.0
     */
    public function lt(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::LT, new Value($value));
    }

    /**
     * Creates a greater-than-or-equal comparison: field >= value.
     *
     * @param string $field The field/property name to compare.
     * @param mixed  $value The scalar lower-bound value (inclusive).
     * @return Comparison
     * @since 1.0
     */
    public function gte(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::GTE, new Value($value));
    }

    /**
     * Creates a less-than-or-equal comparison: field <= value.
     *
     * @param string $field The field/property name to compare.
     * @param mixed  $value The scalar upper-bound value (inclusive).
     * @return Comparison
     * @since 1.0
     */
    public function lte(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::LTE, new Value($value));
    }

    /**
     * Creates an inequality comparison: field != value.
     *
     * @param string $field The field/property name to compare.
     * @param mixed  $value The scalar value to compare against.
     * @return Comparison
     * @since 1.0
     */
    public function neq(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::NEQ, new Value($value));
    }

    /**
     * Creates a null-equality check: field IS NULL.
     *
     * @param string $field The field/property name to test.
     * @return Comparison
     * @since 1.0
     */
    public function is_null(string $field): Comparison
    {
        return new Comparison($field, Comparison::EQ, new Value(null));
    }

    /**
     * Creates a not-null check: field IS NOT NULL.
     *
     * @param string $field The field/property name to test.
     * @return Comparison
     * @since 1.1
     */
    public function is_not_null(string $field): Comparison
    {
        return new Comparison($field, Comparison::NEQ, new Value(null));
    }

    /**
     * Creates an IN comparison: field IN (value1, value2, …).
     *
     * @param string  $field  The field/property name to test.
     * @param mixed[] $values The list of accepted scalar values.
     * @return Comparison
     * @since 1.0
     */
    public function in(string $field, array $values): Comparison
    {
        return new Comparison($field, Comparison::IN, new Value($values));
    }

    /**
     * Creates a NOT IN comparison: field NOT IN (value1, value2, …).
     *
     * @param string  $field  The field/property name to test.
     * @param mixed[] $values The list of excluded scalar values.
     * @return Comparison
     * @since 1.0
     */
    public function not_in(string $field, array $values): Comparison
    {
        return new Comparison($field, Comparison::NIN, new Value($values));
    }

    /**
     * Creates a substring containment check: field CONTAINS value.
     *
     * The field value is cast to string before the comparison.
     *
     * @param string $field The field/property name to test.
     * @param mixed  $value The substring to search for.
     * @return Comparison
     * @since 1.1
     */
    public function contains(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::CONTAINS, new Value($value));
    }

    /**
     * Creates a membership check: value is a member of the collection stored in field.
     *
     * The field is expected to hold an array or Traversable.
     *
     * @param string $field The field/property name that holds a collection.
     * @param mixed  $value The scalar value to look for inside that collection.
     * @return Comparison
     * @since 1.2
     */
    public function member_of(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::MEMBER_OF, new Value($value));
    }

    /**
     * Creates a prefix check: field STARTS WITH value.
     *
     * The field value is cast to string before the comparison.
     *
     * @param string $field The field/property name to test.
     * @param mixed  $value The expected string prefix.
     * @return Comparison
     * @since 1.3
     */
    public function starts_with(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::STARTS_WITH, new Value($value));
    }

    /**
     * Creates a suffix check: field ENDS WITH value.
     *
     * The field value is cast to string before the comparison.
     *
     * @param string $field The field/property name to test.
     * @param mixed  $value The expected string suffix.
     * @return Comparison
     * @since 1.3
     */
    public function ends_with(string $field, mixed $value): Comparison
    {
        return new Comparison($field, Comparison::ENDS_WITH, new Value($value));
    }
}