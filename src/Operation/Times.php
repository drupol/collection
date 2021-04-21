<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use EmptyIterator;
use Generator;
use Iterator;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Times extends AbstractOperation
{
    /**
     * @psalm-return Closure(int): Closure(null|callable(int): (int|T)): Closure(null|Iterator<TKey, T>): Generator<int, int|T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-return Closure(null|callable(int): (int|T)): Closure(null|Iterator<TKey, T>): Generator<int, int|T>
             */
            static fn (int $number = 0): Closure =>
                /**
                 * @psalm-return Closure(null|Iterator<TKey, T>): Generator<int, int|T>
                 */
                static fn (?callable $callback = null): Closure =>
                    /**
                     * @psalm-param null|Iterator<TKey, T> $iterator
                     *
                     * @psalm-return Generator<int, int|T>
                     */
                    static function (?Iterator $iterator = null) use ($number, $callback): Generator {
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
