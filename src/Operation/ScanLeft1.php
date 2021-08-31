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
final class ScanLeft1 implements Operation
{
    /**
     * @pure
     *
     * @template V
     *
     * @param callable(T|V, T, TKey, Iterator<TKey, T>): V $callback
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int|TKey, T|V>
     */
    public function __invoke(callable $callback): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<int|TKey, T|V>
             */
            static function (Iterator $iterator) use ($callback): Iterator {
                $initial = $iterator->current();

                return Pipe::ofTyped3(
                    (new Tail())(),
                    (new Reduction())($callback)($initial),
                    (new Prepend())($initial)
                )($iterator);
            };
    }
}
