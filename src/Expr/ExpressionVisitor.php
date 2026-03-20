<?php

declare (strict_types=1);
namespace Doctrine\Common\Collections\Expr;

/**
 * An Expression visitor walks a graph of expressions and turns them into a
 * query for the underlying implementation.
 */
abstract class Expression_Visitor
{
    /**
     * Converts a comparison expression into the target query language output.
     */
    abstract public function walk_comparison(Comparison $comparison): mixed;
    /**
     * Converts a value expression into the target query language part.
     */
    abstract public function walk_value(Value $value): mixed;
    /**
     * Converts a composite expression into the target query language output.
     */
    abstract public function walk_composite_expression(Composite_Expression $expr): mixed;
    /**
     * Dispatches walking an expression to the appropriate handler.
     */
    public function dispatch(Expression $expr): mixed
    {
        return $expr->visit($this);
    }
}