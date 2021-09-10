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
final class Map extends AbstractOperation
{
    /**
     * @pure
     *
     * @template V
     *
     * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): V): Closure(Iterator<TKey, T>): Iterator<TKey, V>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T=, TKey=, Iterator<TKey, T>=): V $callback
             */
            static fn (callable $callback): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<TKey, V>
                 */
                static function (Iterator $iterator) use ($callback): Iterator {
                    foreach ($iterator as $key => $value) {
                        yield $key => $callback($value, $key, $iterator);
                    }
                };
    }
}
