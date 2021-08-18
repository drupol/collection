<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use Generator;
use Iterator;
use loophp\collection\Iterator\IterableIterator;

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
     *
     * @template UKey
     * @template U
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param iterable<UKey, U> ...$iterables
             *
             * @return Closure(Iterator<TKey, T>): Generator<int, list<T|U>>
             */
            static function (iterable ...$iterables): Closure {
                /** @var Closure(Iterator<TKey, T>): Generator<int, list<T|U>> $pipe */
                $pipe = Pipe::of()(
                    (
                        /**
                         * @param list<Iterator<UKey, U>> $iterables
                         */
                        static fn (array $iterables): Closure =>
                        /**
                         * @param Iterator<TKey, T> $iterator
                         */
                        static fn (Iterator $iterator): Generator => (
                            /**
                             * @param Closure(Iterator<TKey, T>): (Closure(Iterator<UKey, U>): Generator<list<T|U>>) $f
                             */
                            static fn (Closure $f): Closure => (new FoldLeft())()(
                                /**
                                 * @param Iterator<UKey, U> $a
                                 * @param Iterator<TKey, T> $x
                                 */
                                static fn (Iterator $a, Iterator $x): Generator => $f($x)($a)
                            )
                        )(
                            /**
                             * @param (Iterator<TKey, T>|Iterator<UKey, U>) $xs
                             */
                            static fn (Iterator $xs): Closure =>
                            /**
                             * @param Iterator<int, list<T>> $as
                             */
                            static fn (Iterator $as): Generator => FlatMap::of()(
                                /**
                                 * @param list<T> $a
                                 */
                                static fn (array $a): Generator => FlatMap::of()(
                                    /**
                                     * @param T|U $x
                                     *
                                     * @return Generator<int, list<T|U>>
                                     */
                                    static fn ($x): Generator => yield [...$a, $x]
                                )($xs)
                            )($as)
                        )(new ArrayIterator([[]]))(new ArrayIterator([$iterator, ...$iterables]))
                    )(
                        array_map(
                            /**
                             * @param iterable<UKey, U> $iterable
                             *
                             * @return Iterator<UKey, U>
                             */
                            static fn (iterable $iterable): Iterator => new IterableIterator($iterable),
                            $iterables
                        )
                    ),
                    ((new Unwrap())()),
                    ((new Normalize())())
                );

                // Point free style.
                return $pipe;
            };
    }
}
