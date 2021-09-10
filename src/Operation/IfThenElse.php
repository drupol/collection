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
final class IfThenElse extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool): Closure(callable(T=, TKey=, Iterator<TKey, T>=): T): Closure(callable(T=, TKey=, Iterator<TKey, T>=): T): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T=, TKey=, Iterator<TKey, T>=): bool $condition
             *
             * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): T): Closure(callable(T=, TKey=, Iterator<TKey, T>=): T): Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (callable $condition): Closure =>
                /**
                 * @param callable(T=, TKey=, Iterator<TKey, T>=): T $then
                 *
                 * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): T): Closure(Iterator<TKey, T>): Iterator<TKey, T>
                 */
                static fn (callable $then): Closure =>
                    /**
                     * @param callable(T=, TKey=, Iterator<TKey, T>=):T $else
                     *
                     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                     */
                    static function (callable $else) use ($condition, $then): Closure {
                        /** @var Closure(Iterator<TKey, T>): Iterator<TKey, T> $map */
                        $map = (new Map())()(
                            /**
                             * @param T $value
                             * @param TKey $key
                             * @param Iterator<TKey, T> $iterator
                             *
                             * @return T
                             */
                            static fn ($value, $key, Iterator $iterator) => $condition($value, $key, $iterator) ? $then($value, $key, $iterator) : $else($value, $key, $iterator)
                        );

                        // Point free style.
                        return $map;
                    };
    }
}
