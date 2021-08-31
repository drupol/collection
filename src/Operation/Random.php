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
final class Random implements Operation
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
            static function (int $seed): Closure {
                return
                    /**
                     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                     */
                    static function (int $size) use ($seed): Closure {
                        return Pipe::ofTyped2(
                            (new Shuffle())($seed),
                            (new Limit())($size)(0)
                        );
                    };
            };
    }
}
