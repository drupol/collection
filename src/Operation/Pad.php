<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Pad extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int): Closure(T): Closure(Iterator<TKey, T>): Generator<int|TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(T): Closure(Iterator<TKey, T>): Generator<int|TKey, T>
             */
            static fn (int $size): Closure =>
                /**
                 * @param T $padValue
                 *
                 * @return Closure(Iterator<TKey, T>): Generator<int|TKey, T>
                 */
                static fn ($padValue): Closure =>
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return Generator<int|TKey, T>
                     */
                    static function (Iterator $iterator) use ($size, $padValue): Generator {
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
