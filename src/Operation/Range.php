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

use const INF;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Range extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(float  = default):Closure (float=): Closure(float=): Closure(null|Iterator<TKey, T>): Generator<int, float>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(float=): Closure(float=): Closure(null|Iterator<TKey, T>): Generator<int, float>
             */
            static fn (float $start = 0.0): Closure =>
                /**
                 * @return Closure(float=): Closure(null|Iterator<TKey, T>): Generator<int, float>
                 */
                static fn (float $end = INF): Closure =>
                    /**
                     * @return Closure(null|Iterator<TKey, T>): Generator<int, float>
                     */
                    static fn (float $step = 1.0): Closure =>
                        /**
                         * @param Iterator<TKey, T>|null $iterator
                         *
                         * @return Generator<int, float>
                         */
                        static function (?Iterator $iterator = null) use ($start, $end, $step): Generator {
                            for ($current = $start; $current < $end; $current += $step) {
                                yield $current;
                            }
                        };
    }
}
