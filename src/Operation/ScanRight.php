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
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class ScanRight implements Operation
{
    /**
     * @pure
     *
     * @template V
     * @template W
     *
     * @param callable((V|W)=, T=, TKey=, Iterator<TKey, T>=): W $callback
     *
     * @return Closure(V): Closure(Iterator<TKey, T>): Iterator<int|TKey, V|W>
     */
    public function __invoke(callable $callback): Closure
    {
        return
            /**
             * @param V $initial
             *
             * @return Closure(Iterator<TKey, T>): Iterator<int|TKey, V|W>
             */
            static function ($initial) use ($callback): Closure {
                // Point free style.
                return Pipe::ofTyped4(
                    (new Reverse())(),
                    (new Reduction())($callback)($initial),
                    (new Reverse())(),
                    (new Append())($initial)
                );
            };
    }
}
