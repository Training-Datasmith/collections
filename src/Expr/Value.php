<?php

declare (strict_types=1);
namespace Doctrine\Common\Collections\Expr;

use Override;
final readonly class Value implements Expression
{
    public function __construct(private mixed $value)
    {
    }
    public function get_value(): mixed
    {
        return $this->value;
    }
    #[Override]
    public function visit(Expression_Visitor $visitor): mixed
    {
        return $visitor->walk_value($this);
    }
}