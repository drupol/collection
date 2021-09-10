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
final class Nth extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int): Closure(int): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(int): Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (int $step): Closure =>
                /**
                 * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                 */
                static function (int $offset) use ($step): Closure {
                    $filterCallback =
                        /**
                         * @param array{0: TKey, 1: T} $value
                         */
                        static fn (array $value, int $key): bool => (($key % $step) === $offset);

                    // Point free style.
                    return Pipe::ofTyped3(
                        (new Pack())(),
                        (new Filter())()($filterCallback),
                        (new Unpack())()
                    );
                };
    }
}
