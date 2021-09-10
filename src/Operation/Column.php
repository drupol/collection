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
 */
final class Column extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(mixed): Closure(Iterator<TKey, T>): Iterator<int, mixed>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param mixed $column
             *
             * @return Closure(Iterator<TKey, T>): Iterator<int, mixed>
             */
            static function ($column): Closure {
                $filterCallbackBuilder =
                    /**
                     * @param mixed $column
                     */
                    static fn ($column): Closure =>
                        /**
                         * @param T $value
                         * @param TKey $key
                         * @param Iterator<TKey, T> $iterator
                         */
                        static fn ($value, $key, Iterator $iterator): bool => $key === $column;

                // Point free style.
                return Pipe::ofTyped4(
                    (new Transpose())(),
                    (new Filter())()($filterCallbackBuilder($column)),
                    (new Head())(),
                    (new Flatten())()(1)
                );
            };
    }
}
