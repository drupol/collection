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

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Get extends AbstractOperation
{
    /**
     * @pure
     *
     * @template V
     *
     * @return Closure(TKey): Closure(V|null): Closure(Iterator<TKey, T>): Generator<TKey, T|V|null>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param TKey $keyToGet
             *
             * @return Closure(V|null): Closure(Iterator<TKey, T>): Generator<TKey, T|V|null>
             */
            static fn ($keyToGet): Closure =>
                /**
                 * @param V|null $default
                 *
                 * @return Closure(Iterator<TKey, T>): Generator<TKey, T|V|null>
                 */
                static function ($default) use ($keyToGet): Closure {
                    $filterCallback =
                        /**
                         * @param T $value
                         * @param TKey $key
                         */
                        static fn ($value, $key): bool => $key === $keyToGet;

                    /** @var Closure(Iterator<TKey, T>): (Generator<TKey, T|V|null>) $pipe */
                    $pipe = Pipe::of()(
                        (new Filter())()($filterCallback),
                        Append::of()($default),
                        Head::of()
                    );

                    // Point free style.
                    return $pipe;
                };
    }
}
