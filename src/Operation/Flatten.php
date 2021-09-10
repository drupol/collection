<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Iterator\IterableIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Flatten extends AbstractOperation
{
    /**
     * @pure
     *
     * @template UKey
     * @template U
     *
     * @return Closure(int): Closure(Iterator<TKey, (T|iterable<UKey, U>)>): Iterator<TKey|UKey, T|U>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(Iterator<TKey, (T|iterable<UKey, U>)>): Iterator<TKey|UKey, T|U>
             */
            static fn (int $depth): Closure =>
                /**
                 * @param Iterator<TKey, (T|iterable<UKey, U>)> $iterator
                 *
                 * @return Iterator<TKey|UKey, T|U>
                 */
                static function (Iterator $iterator) use ($depth): Iterator {
                    foreach ($iterator as $key => $value) {
                        if (false === is_iterable($value)) {
                            yield $key => $value;

                            continue;
                        }

                        if (1 !== $depth) {
                            $value = (new Flatten())()($depth - 1)(new IterableIterator($value));
                        }

                        yield from $value;
                    }
                };
    }
}
