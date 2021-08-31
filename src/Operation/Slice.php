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
final class Slice implements Operation
{
    /**
     * @pure
     *
     * @return Closure(int=): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(int $offset): Closure
    {
        return
            /**
             * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static function (int $length = -1) use ($offset): Closure {
                $skip = (new Drop())($offset);

                if (-1 === $length) {
                    return $skip;
                }

                return Pipe::ofTyped2(
                    $skip,
                    (new Limit())($length)(0)
                );
            };
    }
}
