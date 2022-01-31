<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use loophp\iterators\IterableIteratorAggregate;
use loophp\iterators\TypedIteratorAggregate;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Strict extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(null|callable(mixed): string): Closure(iterable<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param null|callable(mixed): string $callback
             *
             * @return Closure(iterable<TKey, T>): Generator<TKey, T>
             */
            static fn (?callable $callback = null): Closure =>
                /**
                 * @param iterable<TKey, T> $iterable
                 *
                 * @return Generator<TKey, T>
                 */
                static fn (iterable $iterator): Generator => yield from new TypedIteratorAggregate((new IterableIteratorAggregate($iterator))->getIterator(), $callback);
    }
}
