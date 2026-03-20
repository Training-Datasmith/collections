<?php

declare (strict_types=1);
namespace Doctrine\Common\Collections\Expr;

use InvalidArgumentException;
use Override;
/**
 * Comparison of a field with a value by the given operator.
 */
final readonly class Comparison implements Expression
{
    public const string EQ = '=';
    public const string NEQ = '<>';
    public const string LT = '<';
    public const string LTE = '<=';
    public const string GT = '>';
    public const string GTE = '>=';
    public const string IS = '=';
    // no difference with EQ
    public const string IN = 'IN';
    public const string NIN = 'NIN';
    public const string CONTAINS = 'CONTAINS';
    public const string MEMBER_OF = 'MEMBER_OF';
    public const string STARTS_WITH = 'STARTS_WITH';
    public const string ENDS_WITH = 'ENDS_WITH';
    private const array VALID_OPERATORS = [self::EQ, self::NEQ, self::LT, self::LTE, self::GT, self::GTE, self::IN, self::NIN, self::CONTAINS, self::MEMBER_OF, self::STARTS_WITH, self::ENDS_WITH];
    private Value $value;
    public function __construct(private string $field, private string $op, mixed $value)
    {
        if (!in_array($op, self::VALID_OPERATORS, true)) {
            throw new InvalidArgumentException(sprintf('Unknown comparison operator "%s". Valid operators are: %s', $op, implode(', ', self::VALID_OPERATORS)));
        }
        if (!$value instanceof Value) {
            $value = new Value($value);
        }
        $this->value = $value;
    }
    public function get_field(): string
    {
        return $this->field;
    }
    public function get_value(): Value
    {
        return $this->value;
    }
    public function get_operator(): string
    {
        return $this->op;
    }
    #[Override]
    public function visit(Expression_Visitor $visitor): mixed
    {
        return $visitor->walk_comparison($this);
    }
}