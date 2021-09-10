<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
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
final class Distinct extends AbstractOperation
{
    /**
     * @pure
     *
     * @template U
     *
     * @return Closure(callable(U): Closure(U): bool): Closure(callable(T, TKey): U): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(U): (Closure(U): bool) $comparatorCallback
             *
             * @return Closure(callable(T, TKey): U): Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (callable $comparatorCallback): Closure =>
                /**
                 * @param callable(T, TKey): U $accessorCallback
                 *
                 * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                 */
                static function (callable $accessorCallback) use ($comparatorCallback): Closure {
                    /** @var ArrayIterator<int, array{0: TKey, 1: T}> $stack */
                    $stack = new ArrayIterator();

                    $filter = (new Filter())()(
                        /**
                         * @param T $value
                         * @param TKey $key
                         */
                        static function ($value, $key) use ($comparatorCallback, $accessorCallback, $stack): bool {
                            $matchWhenNot = static fn (): bool => true;
                            $matcher =
                                /**
                                 * @param array{0: TKey, 1: T} $item
                                 */
                                static fn (array $item): bool => $comparatorCallback($accessorCallback($value, $key))($accessorCallback($item[1], $item[0]));

                            $matchFalse = (new MatchOne())()($matchWhenNot)($matcher)($stack);

                            if (true === $matchFalse->current()) {
                                return false;
                            }

                            $stack->append([$key, $value]);

                            return true;
                        }
                    );

                    // Point free style.
                    return $filter;
                };
    }
}
