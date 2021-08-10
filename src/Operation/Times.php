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
use loophp\fpt\FPT;

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
     * @return Closure(int): Closure(null|callable(int): (int|T)): Closure(null|Iterator<TKey, T>): Generator<int, int|T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(null|callable(int): (int|T)): Closure(null|Iterator<TKey, T>): Generator<int, int|T>
             */
            static fn (int $number = 0): Closure =>
                /**
                 * @return Closure(null|Iterator<TKey, T>): Generator<int, int|T>
                 */
                static fn (?callable $callback = null): Closure =>
                    /**
                     * @return Generator<int, int|T>
                     */
                    static function () use ($number, $callback): Generator {
                        if (1 > $number) {
                            return yield from [];
                        }

                        $callback ??= FPT::identity();

                        for ($current = 1; $current <= $number; ++$current) {
                            yield $callback($current);
                        }
                    };
    }
}
