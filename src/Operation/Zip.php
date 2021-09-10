<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
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
     * @return Closure(iterable<mixed, mixed>...): Closure(Iterator<TKey, T>): Iterator<list<TKey|mixed>, list<T|mixed>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param iterable<mixed, mixed> ...$iterables
             *
             * @return Closure(Iterator<TKey, T>): Iterator<list<TKey|mixed>, list<T|mixed>>
             */
            static function (iterable ...$iterables): Closure {
                $buildArrayIterator =
                    /**
                     * @param list<iterable<mixed, mixed>> $iterables
                     */
                    static fn (array $iterables): Closure =>
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return ArrayIterator<int, (Iterator<TKey, T>|IterableIterator<mixed, mixed>)>
                     */
                    static fn (Iterator $iterator): Iterator => new ArrayIterator([
                        $iterator,
                        ...array_map(
                            /**
                             * @param iterable<mixed, mixed> $iterable
                             *
                             * @return IterableIterator<mixed, mixed>
                             */
                            static fn (iterable $iterable): IterableIterator => new IterableIterator($iterable),
                            $iterables
                        ),
                    ]);

                $buildMultipleIterator =
                    /**
                     * @return Closure(ArrayIterator<int, (Iterator<TKey, T>|IterableIterator<mixed, mixed>)>): MultipleIterator
                     */
                    (new Reduce())()(
                        /**
                         * @param Iterator<TKey, T> $iterator
                         */
                        static function (MultipleIterator $acc, Iterator $iterator): MultipleIterator {
                            $acc->attachIterator($iterator);

                            return $acc;
                        }
                    )(new MultipleIterator(MultipleIterator::MIT_NEED_ANY));

                // Point free style.
                return Pipe::ofTyped3(
                    $buildArrayIterator($iterables),
                    $buildMultipleIterator,
                    ((new Flatten())()(1))
                );
            };
    }
}
