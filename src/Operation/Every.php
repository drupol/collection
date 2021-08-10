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
use loophp\collection\Utils\CallbacksArrayReducer;
use loophp\fpt\FPT;
use loophp\fpt\Operator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Every extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(T, TKey, Iterator<TKey, T>...): bool): Closure(callable(T, TKey, Iterator<TKey, T>...): bool): Closure(Iterator<TKey, T>): Generator<TKey, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T, TKey, Iterator<TKey, T>): bool ...$matchers
             *
             * @return Closure(...callable(T, TKey, Iterator<TKey, T>): bool): Closure(Iterator<TKey, T>): Generator<TKey, bool>
             */
            static function (callable ...$matchers): Closure {
                return
                    /**
                     * @param callable(T, TKey, Iterator<TKey, T>): bool ...$callbacks
                     *
                     * @return Closure(Iterator<TKey, T>): Generator<TKey, bool>
                     */
                    static function (callable ...$callbacks) use ($matchers): Closure {
                        $callbackReducer =
                            /**
                             * @param list<callable(T, TKey, Iterator<TKey, T>): bool> $callbacks
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
                             * @param callable(T, TKey, Iterator<TKey, T>): mixed $reducer1
                             *
                             * @return Closure(callable(T, TKey, Iterator<TKey, T>)): Closure(T, TKey, Iterator<TKey, T>): bool
                             */
                            static fn (callable $reducer1): Closure =>
                                /**
                                 * @param callable(T, TKey, Iterator<TKey, T>): mixed $reducer2
                                 *
                                 * @return Closure(T, TKey, Iterator<TKey, T>): bool
                                 */
                                static fn (callable $reducer2): Closure =>
                                    /**
                                     * @param T $value
                                     * @param TKey $key
                                     * @param Iterator<TKey, T> $iterator
                                     */
                                    static fn ($value, $key, Iterator $iterator): bool => FPT::operator()(Operator::OP_NOT_EQUAL)($reducer1($value, $key, $iterator))($reducer2($value, $key, $iterator));

                        /** @var Closure(Iterator<TKey, T>): Generator<TKey, bool> $pipe */
                        $pipe = Pipe::of()(
                            Map::of()($mapCallback($callbackReducer($callbacks))($callbackReducer($matchers))),
                            DropWhile::of()(
                                FPT::compose()(
                                    FPT::operator()(Operator::OP_EQUAL)(true),
                                    FPT::arg()(0)
                                )
                            ),
                            Append::of()(true),
                            Head::of(),
                        );

                        // Point free style.
                        return $pipe;
                    };
            };
    }
}
