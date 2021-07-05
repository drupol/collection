<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;

use function count;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Product extends AbstractOperation
{
    /**
     * @pure
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param iterable<TKey, T> ...$iterables
             *
             * @return Closure(Iterator<TKey, T>): Generator<int, array<int, T>>
             */
            static fn (iterable ...$iterables): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Generator<int, array<int, T>>
                 */
                static function (Iterator $iterator) use ($iterables): Iterator {
                    /** @var Closure(iterable<TKey, T>...): Generator<int, array<int, T>> $cartesian */
                    $cartesian =
                        /**
                         * @param iterable<TKey, T> ...$iterables
                         *
                         * @return Generator<int, array<int, T>>
                         */
                        static function (iterable ...$iterables) use (&$cartesian): Generator {
                            $iterable = array_pop($iterables);

                            if (null === $iterable) {
                                return yield [];
                            }

                            // @todo Find better algo, without recursion.
                            /** @var array<int, T> $item */
                            foreach ($cartesian(...$iterables) as $item) {
                                foreach ($iterable as $value) {
                                    yield $item + [count($item) => $value];
                                }
                            }
                        };

                    return $cartesian($iterator, ...$iterables);
                };
    }
}
