<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
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
final class Scale extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(float): Closure(float): Closure(float): Closure(float): Closure(float): Closure(Iterator<TKey, float|int>): Iterator<TKey, float|int>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(float): Closure(float): Closure(float): Closure(float): Closure(Iterator<TKey, float|int>): Iterator<TKey, float|int>
             */
            static fn (float $lowerBound): Closure =>
                /**
                 * @return Closure(float): Closure(float): Closure(float): Closure(Iterator<TKey, float|int>): Iterator<TKey, float|int>
                 */
                static fn (float $upperBound): Closure =>
                    /**
                     * @return Closure(float): Closure(float): Closure(Iterator<TKey, float|int>): Iterator<TKey, float|int>
                     */
                    static fn (float $wantedLowerBound = 0.0): Closure =>
                        /**
                         * @return Closure(float): Closure(Iterator<TKey, float|int>): Iterator<TKey, float|int>
                         */
                        static fn (float $wantedUpperBound = 1.0): Closure =>
                            /**
                             * @return Closure(Iterator<TKey, float|int>): Iterator<TKey, float|int>
                             */
                            static function (float $base = 0.0) use ($lowerBound, $upperBound, $wantedLowerBound, $wantedUpperBound): Closure {
                                $wantedLowerBound = (0.0 === $wantedLowerBound) ? (0.0 === $base ? 0.0 : 1.0) : $wantedLowerBound;
                                $wantedUpperBound = (1.0 === $wantedUpperBound) ? (0.0 === $base ? 1.0 : $base) : $wantedUpperBound;

                                $mapper = (new Map())()(
                                    /**
                                     * @param float|int $v
                                     */
                                    static function ($v) use ($lowerBound, $upperBound, $wantedLowerBound, $wantedUpperBound, $base): float {
                                        $mx = 0.0 === $base ?
                                            ($v - $lowerBound) / ($upperBound - $lowerBound) :
                                            log($v - $lowerBound, $base) / log($upperBound - $lowerBound, $base);

                                        $mx = $mx === -INF ? 0 : $mx;

                                        return $wantedLowerBound + $mx * ($wantedUpperBound - $wantedLowerBound);
                                    }
                                );

                                $filter = (new Filter())()(
                                    /**
                                     * @param float|int $item
                                     */
                                    static fn ($item): bool => $item >= $lowerBound,
                                    /**
                                     * @param float|int $item
                                     */
                                    static fn ($item): bool => $item <= $upperBound
                                );

                                // Point free style.
                                return Pipe::ofTyped2($filter, $mapper);
                            };
    }
}
