<?php

declare(strict_types=1);

namespace Doctrine\Tests\Common\Collections\Security;

use Doctrine\Common\Collections\Expr\Closure_Expression_Visitor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Security regression tests for the dotted field-path depth limit introduced
 * in Closure_Expression_Visitor::get_object_field_value().
 *
 * Without the depth cap an adversary could craft a deeply nested field path
 * (e.g. "a.b.c.d…") that causes unbounded recursion, leading to a stack
 * overflow or resource exhaustion.
 *
 * The fix caps traversal at 10 levels and throws RuntimeException when exceeded.
 */
#[CoversClass(Closure_Expression_Visitor::class)]
final class Field_Path_Depth_Test extends TestCase
{
    /**
     * A path with exactly 10 levels of nesting should be allowed.
     */
    public function test_path_at_maximum_allowed_depth_resolves_successfully(): void
    {
        // Build a chain: $obj->a->a->a->a->a->a->a->a->a->a  (10 dots = 11 segments, but
        // the check triggers when substr_count > 10, so 10 dots = exactly the boundary)
        $leaf = new \stdClass();
        $leaf->value = 'found';

        $inner = $leaf;
        for ($i = 0; $i < 9; $i++) {
            $wrapper = new \stdClass();
            $wrapper->a = $inner;
            $inner = $wrapper;
        }
        // $inner is the top-level object; path is "a.a.a.a.a.a.a.a.a.value" (9 dots)
        $path = implode('.', array_fill(0, 9, 'a')) . '.value';

        $result = Closure_Expression_Visitor::get_object_field_value($inner, $path);
        self::assertSame('found', $result);
    }

    /**
     * A path with more than 10 dots must be rejected to prevent stack overflow via
     * recursive calls from adversarial input.
     *
     * This validates the security fix: paths that exceed the allowed nesting depth
     * throw a RuntimeException rather than recursing indefinitely.
     */
    public function test_path_exceeding_maximum_depth_throws_runtime_exception(): void
    {
        // 11 dots = 12 path segments — exceeds the cap of 10
        $deeply_nested_path = implode('.', array_fill(0, 12, 'x'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Field path depth exceeds maximum allowed nesting of 10 levels.');

        Closure_Expression_Visitor::get_object_field_value(new \stdClass(), $deeply_nested_path);
    }

    /**
     * A path with exactly 11 dots (boundary + 1) also triggers the guard.
     */
    public function test_path_with_eleven_dots_triggers_depth_guard(): void
    {
        $path = str_repeat('a.', 11) . 'b';  // 11 dots

        $this->expectException(RuntimeException::class);

        Closure_Expression_Visitor::get_object_field_value(new \stdClass(), $path);
    }

    /**
     * Flat field names (no dots) must still resolve normally after the depth guard
     * was introduced, confirming the fix did not break the happy path.
     */
    public function test_flat_field_name_resolves_without_depth_guard_interference(): void
    {
        $obj = new \stdClass();
        $obj->name = 'Alice';

        $result = Closure_Expression_Visitor::get_object_field_value($obj, 'name');
        self::assertSame('Alice', $result);
    }

    /**
     * Array values are also subject to the depth guard for dotted paths.
     */
    public function test_deeply_nested_path_on_array_throws_runtime_exception(): void
    {
        $path = implode('.', array_fill(0, 12, 'key'));

        $this->expectException(RuntimeException::class);

        Closure_Expression_Visitor::get_object_field_value([], $path);
    }
}
