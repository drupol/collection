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
final class Slice extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int): Closure(int=): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(int=): Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (int $offset): Closure =>
                /**
                 * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                 */
                static function (int $length = -1) use ($offset): Closure {
                    /** @var Closure(Iterator<TKey, T>): Iterator<TKey, T> $skip */
                    $skip = (new Drop())()($offset);

                    if (-1 === $length) {
                        return $skip;
                    }

                    // Point free style.
                    return Pipe::ofTyped2(
                        $skip,
                        (new Limit())()($length)(0)
                    );
                };
    }
}
