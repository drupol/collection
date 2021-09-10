<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use EmptyIterator;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Times extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int): Closure(null|callable(int): (int|T)): Closure(null|Iterator<TKey, T>): Iterator<int, int|T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(null|callable(int): (int|T)): Closure(null|Iterator<TKey, T>): Iterator<int, int|T>
             */
            static fn (int $number = 0): Closure =>
                /**
                 * @return Closure(null|Iterator<TKey, T>): Iterator<int, int|T>
                 */
                static fn (?callable $callback = null): Closure =>
                    /**
                     * @param Iterator<TKey, T>|null $iterator
                     *
                     * @return Iterator<int, int|T>
                     */
                    static function (?Iterator $iterator = null) use ($number, $callback): Iterator {
                        if (1 > $number) {
                            return new EmptyIterator();
                        }

                        $callback ??= static fn (int $value): int => $value;

                        for ($current = 1; $current <= $number; ++$current) {
                            yield $callback($current);
                        }
                    };
    }
}
