<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;

use function count;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 */
final class Product extends AbstractOperation
{
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param iterable<TKey, T> ...$iterables
             *
             * @psalm-return Closure(Iterator<TKey, T>): Generator<int, array<int, T>>
             */
            static fn (iterable ...$iterables): Closure =>
                /**
                 * @psalm-param Iterator<TKey, T> $iterator
                 *
                 * @psalm-return Generator<int, array<int, T>>
                 */
                static function (Iterator $iterator) use ($iterables): Generator {
                    /** @psalm-var Closure(iterable<TKey, T>...): Generator<int, array<int, T>> $cartesian */
                    $cartesian =
                        /**
                         * @param array<int, iterable> ...$iterables
                         *
                         * @psalm-param iterable<TKey, T> ...$iterables
                         *
                         * @psalm-return Generator<int, array<int, T>>
                         */
                        static function (iterable ...$iterables) use (&$cartesian): Generator {
                            $iterable = array_pop($iterables);

                            if (null === $iterable) {
                                return yield [];
                            }

                            // @todo Find better algo, without recursion.
                            /** @psalm-var array<int, T> $item */
                            foreach ($cartesian(...$iterables) as $item) {
                                foreach ($iterable as $value) {
                                    yield $item + [count($item) => $value];
                                }
                            }
                        };

                    return yield from $cartesian($iterator, ...$iterables);
                };
    }
}
