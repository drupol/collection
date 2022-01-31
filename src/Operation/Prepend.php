<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use loophp\iterators\ConcatIterableAggregate;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Prepend extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T...): Closure(iterable<TKey, T>): iterable<int|TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T ...$items
             *
             * @return Closure(iterable<TKey, T>): iterable<int|TKey, T>
             */
            static fn (...$items): Closure =>
                /**
                 * @param iterable<TKey, T> $iterable
                 *
                 * @return Generator<int|TKey, T>
                 */
                static fn (iterable $iterable): Generator => yield from new ConcatIterableAggregate([$items, $iterable]);
    }
}
