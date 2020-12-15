<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
final class Reverse extends AbstractOperation
{
    /**
     * @psalm-return Closure(Iterator<TKey, T>): Generator<TKey, T, mixed, void>
     */
    public function __invoke(): Closure
    {
        /**
         * @param array $carry
         * @psalm-param array<int, array{0: TKey, 1: T}> $carry
         *
         * @param array $value
         * @psalm-param array{0: TKey, 1: T} $value
         *
         * @psalm-return array<int, array{0: TKey, 1: T}>
         */
        $callback = static fn (array $carry, array $value): array => [...$value, ...$carry];

        /** @psalm-var Closure(Iterator<TKey, T>): Generator<TKey, T> $pipe */
        $pipe = Pipe::of()(
            Pack::of(),
            Wrap::of(),
            FoldLeft1::of()($callback),
            Unwrap::of(),
            Unpack::of()
        );

        // Point free style.
        return $pipe;
    }
}
