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
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class FlatMap extends AbstractOperation
{
    /**
     * @pure
     *
     * @template IKey
     * @template IValue
     *
     * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): iterable<IKey, IValue>): Closure(Iterator<TKey, T>): Iterator<IKey, IValue>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T=, TKey=, Iterator<TKey, T>=): iterable<IKey, IValue> $callback
             */
            static function (callable $callback): Closure {
                // Point free style
                return Pipe::ofTyped2(
                    (new Map())()($callback),
                    (new Flatten())()(1)
                );
            };
    }
}
