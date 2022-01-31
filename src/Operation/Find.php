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
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Find extends AbstractOperation
{
    /**
     * @pure
     *
     * @template V
     *
     * @return Closure(V): Closure(callable(T=, TKey=, iterable<TKey, T>=): bool ...): Closure(iterable<TKey, T>): Generator<TKey, T|V>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param V $default
             *
             * @return Closure(callable(T=, TKey=, iterable<TKey, T>=): bool ...): Closure(iterable<TKey, T>): Generator<TKey, T|V>
             */
            static fn ($default): Closure =>
                /**
                 * @param callable(T=, TKey=, iterable<TKey, T>=): bool ...$callbacks
                 *
                 * @return Closure(iterable<TKey, T>): Generator<TKey, T|V>
                 */
                static function (callable ...$callbacks) use ($default): Closure {
                    /** @var Closure(iterable<TKey, T>): Generator<TKey, T|V> $pipe */
                    $pipe = (new Pipe())()(
                        (new Filter())()(...$callbacks),
                        (new Append())()($default),
                        (new Head())(),
                    );

                    // Point free style.
                    return $pipe;
                };
    }
}
