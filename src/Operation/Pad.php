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
final class Pad implements Operation
{
    /**
     * @pure
     *
     * @return Closure(T): Closure(Iterator<TKey, T>): Iterator<int|TKey, T>
     */
    public function __invoke(int $size): Closure
    {
        return
            /**
             * @param T $padValue
             *
             * @return Closure(Iterator<TKey, T>): Iterator<int|TKey, T>
             */
            static fn ($padValue): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<int|TKey, T>
                 */
                static function (Iterator $iterator) use ($size, $padValue): Iterator {
                    $y = 0;

                    foreach ($iterator as $key => $value) {
                        ++$y;

                        yield $key => $value;
                    }

                    while ($y++ < $size) {
                        yield $padValue;
                    }
                };
    }
}
