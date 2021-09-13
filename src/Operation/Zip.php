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
use MultipleIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Zip extends AbstractOperation
{
    /**
     * @pure
     *
     * @template UKey
     * @template U
     *
     * @return Closure(iterable<UKey, U>...): Closure(Iterator<TKey, T>): Iterator<list<TKey|UKey>, list<T|U>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param iterable<UKey, U> ...$iterables
             *
             * @return Closure(Iterator<TKey, T>): Iterator<list<TKey|UKey>, list<T|U>>
             */
            static function (iterable ...$iterables): Closure {
                $buildArrayIterator =
                    /**
                     * @param list<iterable<UKey, U>> $iterables
                     */
                    static fn (array $iterables): Closure =>
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return ArrayIterator<int, (Iterator<TKey, T>|IterableIterator<UKey, U>)>
                     */
                    static fn (Iterator $iterator): Iterator => new ArrayIterator([
                        $iterator,
                        ...array_map(
                            /**
                             * @param iterable<UKey, U> $iterable
                             *
                             * @return IterableIterator<UKey, U>
                             */
                            static fn (iterable $iterable): IterableIterator => new IterableIterator($iterable),
                            $iterables
                        ),
                    ]);

                $buildMultipleIterator =
                    /**
                     * @return Closure(ArrayIterator<int, (Iterator<TKey, T>|IterableIterator<UKey, U>)>): MultipleIterator
                     */
                    Reduce::of()(
                        /**
                         * @param Iterator<TKey, T> $iterator
                         */
                        static function (MultipleIterator $acc, Iterator $iterator): MultipleIterator {
                            $acc->attachIterator($iterator);

                            return $acc;
                        }
                    )(new MultipleIterator(MultipleIterator::MIT_NEED_ANY));

                /** @var Closure(Iterator<TKey, T>): Generator<list<TKey|UKey>, list<T|U>> $pipe */
                $pipe = Pipe::of()(
                    $buildArrayIterator($iterables),
                    $buildMultipleIterator,
                    ((new Flatten())()(1))
                );

                // Point free style.
                return $pipe;
            };
    }
}
