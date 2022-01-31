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
final class Merge extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(iterable<TKey, T> ...$sources): Closure(iterable<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param iterable<TKey, T> ...$sources
             */
            static fn (iterable ...$sources): Closure =>
                /**
                 * @param iterable<TKey, T> $iterable
                 *
                 * @return Generator<TKey, T>
                 */
                static fn (iterable $iterable): Generator => yield from new ConcatIterableAggregate([$iterable, ...array_values($sources)]);
    }
}
