<?php

declare (strict_types=1);
namespace Doctrine\Common\Collections;

use Doctrine\Common\Collections\Expr\Composite_Expression;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Deprecations\Deprecation;
use function func_num_args;
/**
 * Criteria for filtering, sorting, and paginating Selectable collections.
 *
 * A Criteria is a backend-independent query description. The same instance can be
 * applied to an in-memory Array_Collection (via Closure_Expression_Visitor) or
 * translated to a SQL/DQL WHERE clause by ORM/ODM implementations of Selectable.
 *
 * Typical usage:
 * <code>
 *   $criteria = Criteria::create()
 *       ->where(Criteria::expr()->eq('status', 'active'))
 *       ->order_by(['created_at' => Order::Descending])
 *       ->set_first_result(0)
 *       ->set_max_results(20);
 *
 *   $results = $collection->matching($criteria);
 * </code>
 *
 * @phpstan-consistent-constructor
 * @since 1.0
 */
final class Criteria
{
    private static Expression_Builder|null $expression_builder = null;
    /** @var array<string, Order> */
    private array $orderings = [];
    private int|null $first_result = null;
    private int|null $max_results = null;
    /**
     * Factory method that creates a new empty Criteria instance.
     *
     * Equivalent to `new static()` but allows fluent chaining:
     * `Criteria::create()->where(...)`.
     *
     * @return static A new Criteria with no expression, no orderings, first_result=0, max_results=null.
     *
     * @since 1.0
     */
    public static function create(): static
    {
        if (func_num_args() === 1) {
            Deprecation::trigger('doctrine/collections', 'https://github.com/doctrine/collections/pull/486', 'The `accessRawFieldValues` parameter passed to %s is deprecated and a no-op. You can remove it.', __METHOD__);
        }
        return new static();
    }
    /**
     * Returns the shared Expression_Builder instance.
     *
     * The builder is a stateless singleton; it is safe to use the same instance
     * across multiple threads (PHP is single-threaded) and across requests.
     *
     * @return Expression_Builder The shared expression builder.
     *
     * @see Expression_Builder For the full list of available comparison methods.
     *
     * @since 1.0
     */
    public static function expr(): Expression_Builder
    {
        if (self::$expression_builder === null) {
            self::$expression_builder = new Expression_Builder();
        }
        return self::$expression_builder;
    }
    /**
     * Construct a new Criteria.
     *
     * @param array<string, Order>|null $orderings
     */
    public function __construct(private Expression|null $expression = null, array|null $orderings = null, int $first_result = 0, int|null $max_results = null)
    {
        if (func_num_args() === 5) {
            Deprecation::trigger('doctrine/collections', 'https://github.com/doctrine/collections/pull/486', 'The `accessRawFieldValues` parameter passed to %s is deprecated and a no-op. You can remove it.', __METHOD__);
        }
        $this->set_first_result($first_result);
        $this->set_max_results($max_results);
        if ($orderings === null) {
            return;
        }
        $this->order_by($orderings);
    }
    /**
     * Sets the where expression to evaluate when this Criteria is searched for.
     *
     * @return $this
     */
    public function where(Expression $expression): static
    {
        $this->expression = $expression;
        return $this;
    }
    /**
     * Appends the where expression to evaluate when this Criteria is searched for
     * using an AND with previous expression.
     *
     * @return $this
     */
    public function and_where(Expression $expression): static
    {
        if ($this->expression === null) {
            return $this->where($expression);
        }
        $this->expression = new Composite_Expression(Composite_Expression::TYPE_AND, [$this->expression, $expression]);
        return $this;
    }
    /**
     * Appends the where expression to evaluate when this Criteria is searched for
     * using an OR with previous expression.
     *
     * @return $this
     */
    public function or_where(Expression $expression): static
    {
        if ($this->expression === null) {
            return $this->where($expression);
        }
        $this->expression = new Composite_Expression(Composite_Expression::TYPE_OR, [$this->expression, $expression]);
        return $this;
    }
    /**
     * Gets the expression attached to this Criteria.
     */
    public function get_where_expression(): Expression|null
    {
        return $this->expression;
    }
    /**
     * Gets the current orderings of this Criteria.
     *
     * @return array<string, Order>
     */
    public function orderings(): array
    {
        return $this->orderings;
    }
    /**
     * Sets the ordering of the result of this Criteria.
     *
     * Keys are field and values are the order, being a valid Order enum case.
     *
     * @see Order::Ascending
     * @see Order::Descending
     *
     * @param array<string, Order> $orderings
     *
     * @return $this
     */
    public function order_by(array $orderings): static
    {
        $this->orderings = $orderings;
        return $this;
    }
    /**
     * Gets the current first result option of this Criteria.
     */
    public function get_first_result(): int|null
    {
        return $this->first_result;
    }
    /**
     * Sets the zero-based offset of the first result to return.
     *
     * Combined with set_max_results(), this enables cursor-style pagination over
     * a Selectable collection.
     *
     * @param int $first_result The zero-based offset (0 = start from the first element).
     *
     * @return $this
     *
     * @since 1.0
     */
    public function set_first_result(int $first_result): static
    {
        $this->first_result = $first_result;
        return $this;
    }

    /**
     * Returns the maximum number of results to return, or null for no limit.
     *
     * @return int|null The current max_results setting.
     *
     * @since 1.0
     */
    public function get_max_results(): int|null
    {
        return $this->max_results;
    }

    /**
     * Sets the maximum number of results this Criteria should return.
     *
     * Pass null to remove the limit and return all matching elements.
     *
     * @param int|null $max_results Maximum result count, or null for unlimited.
     *
     * @return $this
     *
     * @since 1.0
     */
    public function set_max_results(int|null $max_results): static
    {
        $this->max_results = $max_results;
        return $this;
    }
}