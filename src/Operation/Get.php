<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Get implements Operation
{
    /**
     * @pure
     *
     * @return Closure(TKey): Closure (T|null): Closure(Iterator<TKey, T>): Iterator<TKey, T|null>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param TKey $keyToGet
             *
             * @return Closure(T|null): Closure(Iterator<TKey, T>): Iterator<TKey, T|null>
             */
            static fn ($keyToGet): Closure =>
                /**
                 * @param T|null $default
                 *
                 * @return Closure(Iterator<TKey, T>): Iterator<TKey, T|null>
                 */
                static function ($default) use ($keyToGet): Closure {
                    $filterCallback =
                        /**
                         * @param T $value
                         * @param TKey $key
                         */
                        static fn ($value, $key): bool => $key === $keyToGet;

                    return Pipe::ofTyped3(
                        (new Filter())($filterCallback),
                        (new Append())($default),
                        (new Head())
                    );
                };
    }
}
