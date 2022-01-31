<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;

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
     * @return Closure(int): Closure(T): Closure(iterable<TKey, T>): Generator<int|TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(T): Closure(iterable<TKey, T>): Generator<int|TKey, T>
             */
            static fn (int $size): Closure =>
                /**
                 * @param T $padValue
                 *
                 * @return Closure(iterable<TKey, T>): Generator<int|TKey, T>
                 */
                static fn ($padValue): Closure =>
                    /**
                     * @param iterable<TKey, T> $iterable
                     *
                     * @return Generator<int|TKey, T>
                     */
                    static function (iterable $iterable) use ($size, $padValue): Generator {
                        $y = 0;

                        foreach ($iterable as $key => $value) {
                            ++$y;

                            yield $key => $value;
                        }

                        while ($y++ < $size) {
                            yield $padValue;
                        }
                    };
    }
}
