<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Utils\CallbacksArrayReducer;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class MatchOne extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): Closure(Iterator<TKey, T>): Iterator<TKey, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T=, TKey=, Iterator<TKey, T>=): bool ...$matchers
             *
             * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): Closure(Iterator<TKey, T>): Iterator<TKey, bool>
             */
            static function (callable ...$matchers): Closure {
                return
                    /**
                     * @param callable(T=, TKey=, Iterator<TKey, T>=): bool ...$callbacks
                     *
                     * @return Closure(Iterator<TKey, T>): Iterator<TKey, bool>
                     */
                    static function (callable ...$callbacks) use ($matchers): Closure {
                        $callbackReducer =
                            /**
                             * @param list<callable(T=, TKey=, Iterator<TKey, T>=): bool> $callbacks
                             *
                             * @return Closure(T, TKey, Iterator<TKey, T>): bool
                             */
                            static fn (array $callbacks): Closure =>
                                /**
                                 * @param T $current
                                 * @param TKey $key
                                 * @param Iterator<TKey, T> $iterator
                                 */
                                static fn ($current, $key, Iterator $iterator): bool => CallbacksArrayReducer::or()($callbacks, $current, $key, $iterator);

                        $mapCallback =
                            /**
                             * @param callable(T=, TKey=, Iterator<TKey, T>=): mixed $reducer1
                             *
                             * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): mixed): Closure(T, TKey, Iterator<TKey, T>): bool
                             */
                            static fn (callable $reducer1): Closure =>
                                /**
                                 * @param callable(T=, TKey=, Iterator<TKey, T>=): mixed $reducer2
                                 *
                                 * @return Closure(T, TKey, Iterator<TKey, T>): bool
                                 */
                                static fn (callable $reducer2): Closure =>
                                    /**
                                     * @param T $value
                                     * @param TKey $key
                                     * @param Iterator<TKey, T> $iterator
                                     */
                                    static fn ($value, $key, Iterator $iterator): bool => $reducer1($value, $key, $iterator) === $reducer2($value, $key, $iterator);

                        // Point free style.
                        return Pipe::ofTyped4(
                            (new Map())()($mapCallback($callbackReducer($callbacks))($callbackReducer($matchers))),
                            (new DropWhile())()(static fn (bool $value): bool => false === $value),
                            (new Append())()(false),
                            (new Head())()
                        );
                    };
            };
    }
}
