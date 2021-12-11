<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use InvalidArgumentException;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Intersperse extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T): Closure(int): Closure(int): Closure(Iterator<TKey, T>): Generator<int|TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T $element
             *
             * @return Closure(int): Closure(int): Closure(Iterator<TKey, T>): Generator<int|TKey, T>
             */
            static fn ($element): Closure =>
                /**
                 * @return Closure(int): Closure(Iterator<TKey, T>): Generator<int|TKey, T>
                 */
                static fn (int $atEvery): Closure =>
                    /**
                     * @return Closure(Iterator<TKey, T>): Generator<int|TKey, T>
                     */
                    static fn (int $startAt): Closure =>
                        /**
                         * @param Iterator<TKey, T> $iterator
                         *
                         * @return Generator<int|TKey, T>
                         */
                        static function (Iterator $iterator) use ($element, $atEvery, $startAt): Generator {
                            if (0 > $atEvery) {
                                throw new InvalidArgumentException(
                                    'The second parameter must be a positive integer.'
                                );
                            }

                            if (0 > $startAt) {
                                throw new InvalidArgumentException(
                                    'The third parameter must be a positive integer.'
                                );
                            }

                            $current = 0;

                            foreach ($iterator as $key => $value) {
                                if ($startAt === $current++) {
                                    $startAt += $atEvery;
                                    yield $element;
                                }

                                yield $key => $value;
                            }
                        };
    }
}
