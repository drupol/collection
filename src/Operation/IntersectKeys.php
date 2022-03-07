<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;

use function in_array;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class IntersectKeys extends AbstractOperation
{
    /**
     * @return Closure(TKey...): Closure(iterable<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param TKey ...$keys
             *
             * @return Closure(iterable<TKey, T>): Generator<TKey, T>
             */
            static function (...$keys): Closure {
                $filter = (new Filter())()(
                    /**
                     * @param T $value
                     * @param TKey $key
                     */
                    static fn ($value, $key): bool => in_array($key, $keys, true)
                );

                // Point free style.
                return $filter;
            };
    }
}
