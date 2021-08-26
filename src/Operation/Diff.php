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

use function in_array;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Diff extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T...): Closure(Iterator<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T ...$values
             *
             * @return Closure(Iterator<TKey, T>): Generator<TKey, T>
             */
            static function (...$values): Closure {
                $filterCallbackFactory =
                    /**
                     * @param list<T> $values
                     */
                    static fn (array $values): Closure =>
                        /**
                         * @param T $value
                         */
                        static fn ($value): bool => false === in_array($value, $values, true);

                $filter = (new Filter())()($filterCallbackFactory($values));

                // Point free style.
                return $filter;
            };
    }
}
