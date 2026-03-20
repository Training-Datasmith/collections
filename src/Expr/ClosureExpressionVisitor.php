<?php

declare (strict_types=1);
namespace Doctrine\Common\Collections\Expr;

use function array_all;
use function array_any;
use Closure;
use Doctrine\Deprecations\Deprecation;
use function explode;
use function func_num_args;
use function in_array;
use function is_array;
use function is_scalar;
use function iterator_to_array;
use Override;
use ReflectionClass;
use RuntimeException;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function substr_count;
/**
 * Walks an expression graph and turns it into a PHP closure.
 *
 * This closure can be used with {@Collection#filter()} and is used internally
 * by {@ArrayCollection#select()}.
 */
final class Closure_Expression_Visitor extends Expression_Visitor
{
    /**
     * Accesses the raw field value of a given object.
     *
     * @param object|mixed[] $object
     */
    public static function get_object_field_value(object|array $object, string $field): mixed
    {
        if (func_num_args() === 3) {
            Deprecation::trigger('doctrine/collections', 'https://github.com/doctrine/collections/pull/486', 'The `accessRawFieldValues` parameter passed to %s is deprecated and a no-op. You can remove it.', __METHOD__);
        }
        if (str_contains($field, '.')) {
            if (substr_count($field, '.') > 10) {
                throw new RuntimeException('Field path depth exceeds maximum allowed nesting of 10 levels.');
            }
            [$field, $sub_field] = explode('.', $field, 2);
            $object = self::get_object_field_value($object, $field);
            return self::get_object_field_value($object, $sub_field);
        }
        if (is_array($object)) {
            return $object[$field];
        }
        $reflection_class = new ReflectionClass($object);
        while ($reflection_class && !$reflection_class->has_property($field)) {
            $reflection_class = $reflection_class->get_parent_class();
        }
        if ($reflection_class === false) {
            throw new RuntimeException(sprintf('Field "%s" does not exist in class "%s"', $field, $object::class));
        }
        $property = $reflection_class->get_property($field);
        return $property->get_raw_value($object);
    }
    /**
     * Helper for sorting arrays of objects based on multiple fields + orientations.
     */
    public static function sort_by_field(string $name, int $orientation = 1, Closure|null $next = null): Closure
    {
        if (func_num_args() === 4) {
            Deprecation::trigger('doctrine/collections', 'https://github.com/doctrine/collections/pull/486', 'The `accessRawFieldValues` parameter passed to %s is deprecated and a no-op. You can remove it.', __METHOD__);
        }
        if (!$next) {
            $next = static fn(): int => 0;
        }
        return static function (mixed $a, mixed $b) use ($name, $next, $orientation): int {
            $a_value = Closure_Expression_Visitor::get_object_field_value($a, $name);
            $b_value = Closure_Expression_Visitor::get_object_field_value($b, $name);
            if ($a_value === $b_value) {
                return $next($a, $b);
            }
            return ($a_value > $b_value ? 1 : -1) * $orientation;
        };
    }
    #[Override]
    public function walk_comparison(Comparison $comparison): Closure
    {
        $field = $comparison->get_field();
        $value = $comparison->get_value()->get_value();
        return match ($comparison->get_operator()) {
            Comparison::EQ => static fn(object|array $object): bool => self::get_object_field_value($object, $field) === $value,
            Comparison::NEQ => static fn(object|array $object): bool => self::get_object_field_value($object, $field) !== $value,
            Comparison::LT => static fn(object|array $object): bool => self::get_object_field_value($object, $field) < $value,
            Comparison::LTE => static fn(object|array $object): bool => self::get_object_field_value($object, $field) <= $value,
            Comparison::GT => static fn(object|array $object): bool => self::get_object_field_value($object, $field) > $value,
            Comparison::GTE => static fn(object|array $object): bool => self::get_object_field_value($object, $field) >= $value,
            Comparison::IN => static function (object|array $object) use ($field, $value): bool {
                $field_value = Closure_Expression_Visitor::get_object_field_value($object, $field);
                return in_array($field_value, $value, is_scalar($field_value));
            },
            Comparison::NIN => static function (object|array $object) use ($field, $value): bool {
                $field_value = Closure_Expression_Visitor::get_object_field_value($object, $field);
                return !in_array($field_value, $value, is_scalar($field_value));
            },
            Comparison::CONTAINS => static fn(object|array $object): bool => str_contains((string) self::get_object_field_value($object, $field), (string) $value),
            Comparison::MEMBER_OF => static function (object|array $object) use ($field, $value): bool {
                $field_values = Closure_Expression_Visitor::get_object_field_value($object, $field);
                if (!is_array($field_values)) {
                    $field_values = iterator_to_array($field_values);
                }
                return in_array($value, $field_values, true);
            },
            Comparison::STARTS_WITH => static fn(object|array $object): bool => str_starts_with((string) self::get_object_field_value($object, $field), (string) $value),
            Comparison::ENDS_WITH => static fn(object|array $object): bool => str_ends_with((string) self::get_object_field_value($object, $field), (string) $value),
            default => throw new RuntimeException('Unknown comparison operator: ' . $comparison->get_operator()),
        };
    }
    #[Override]
    public function walk_value(Value $value): mixed
    {
        return $value->get_value();
    }
    #[Override]
    public function walk_composite_expression(Composite_Expression $expr): Closure
    {
        $expression_list = [];
        foreach ($expr->get_expression_list() as $child) {
            $expression_list[] = $this->dispatch($child);
        }
        return match ($expr->get_type()) {
            Composite_Expression::TYPE_AND => $this->and_expressions($expression_list),
            Composite_Expression::TYPE_OR => $this->or_expressions($expression_list),
            Composite_Expression::TYPE_NOT => $this->not_expression($expression_list),
            default => throw new RuntimeException('Unknown composite ' . $expr->get_type()),
        };
    }
    /** @param callable[] $expressions */
    private function and_expressions(array $expressions): Closure
    {
        return static fn(object $object): bool => array_all($expressions, static fn(callable $expression): bool => (bool) $expression($object));
    }
    /** @param callable[] $expressions */
    private function or_expressions(array $expressions): Closure
    {
        return static fn(object $object): bool => array_any($expressions, static fn(callable $expression): bool => (bool) $expression($object));
    }
    /** @param callable[] $expressions */
    private function not_expression(array $expressions): Closure
    {
        return static fn(object $object): bool => !$expressions[0]($object);
    }
}