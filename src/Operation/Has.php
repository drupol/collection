<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Has extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): T ...): Closure(Iterator<TKey, T>): Iterator<TKey, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T=, TKey=, Iterator<TKey, T>=): T ...$callbacks
             *
             * @return Closure(Iterator<TKey, T>): Iterator<TKey, bool>
             */
            static function (callable ...$callbacks): Closure {
                /** @var Closure(Iterator<TKey, T>): Iterator<TKey, bool> $pipe */
                $pipe = (new MatchOne())()(static fn (): bool => true)(
                    ...array_map(
                        static fn (callable $callback): callable =>
                            /**
                             * @param T $value
                             * @param TKey $key
                             * @param Iterator<TKey, T> $iterator
                             */
                            static fn ($value, $key, Iterator $iterator): bool => $callback($value, $key, $iterator) === $value,
                        $callbacks
                    )
                );

                // Point free style.
                return $pipe;
            };
    }
}
