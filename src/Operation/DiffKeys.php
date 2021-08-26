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
final class DiffKeys extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(TKey...): Closure(Iterator<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param TKey ...$keys
             *
             * @return Closure(Iterator<TKey, T>): Generator<TKey, T>
             */
            static function (...$keys): Closure {
                $filterCallbackFactory =
                    /**
                     * @param list<TKey> $keys
                     */
                    static fn (array $keys): Closure =>
                        /**
                         * @param T $value
                         * @param TKey $key
                         */
                        static fn ($value, $key): bool => false === in_array($key, $keys, true);

                $filter = (new Filter())()($filterCallbackFactory($keys));

                // Point free style.
                return $filter;
            };
    }
}
