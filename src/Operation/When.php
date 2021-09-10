<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class When extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(Iterator<TKey, T>): bool): Closure(callable(Iterator<TKey, T>): iterable<TKey, T>): Closure(callable(Iterator<TKey, T>): iterable<TKey, T>): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(Iterator<TKey, T>): bool $predicate
             *
             * @return Closure(callable(Iterator<TKey, T>): iterable<TKey, T>): Closure(callable(Iterator<TKey, T>): iterable<TKey, T>): Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (callable $predicate): Closure =>
                /**
                 * @param callable(Iterator<TKey, T>): iterable<TKey, T> $whenTrue
                 *
                 * @return Closure(callable(Iterator<TKey, T>): iterable<TKey, T>): Closure(Iterator<TKey, T>): Iterator<TKey, T>
                 */
                static fn (callable $whenTrue): Closure =>
                    /**
                     * @param callable(Iterator<TKey, T>): iterable<TKey, T> $whenFalse
                     *
                     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                     */
                    static fn (callable $whenFalse): Closure =>
                        /**
                         * @param Iterator<TKey, T> $iterator
                         *
                         * @return Iterator<TKey, T>
                         */
                        static fn (Iterator $iterator): Iterator => yield from (true === $predicate($iterator)) ? $whenTrue($iterator) : $whenFalse($iterator);
    }
}
