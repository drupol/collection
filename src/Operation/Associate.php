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
final class Associate extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(TKey, TKey, T, Iterator<TKey, T>): (T|TKey) ...): Closure((callable(T, TKey, T, Iterator<TKey, T>): (T|TKey))...): Closure(Iterator<TKey, T>): Iterator<TKey|T, T|TKey>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(TKey, TKey, T, Iterator<TKey, T>): (T|TKey) ...$callbackForKeys
             *
             * @return Closure((callable(T, TKey, T, Iterator<TKey, T>): (T|TKey))...): Closure(Iterator<TKey, T>): Iterator<TKey|T, T|TKey>
             */
            static fn (callable ...$callbackForKeys): Closure =>
                /**
                 * @param callable(T, TKey, T, Iterator<TKey, T>): (T|TKey) ...$callbackForValues
                 *
                 * @return Closure(Iterator<TKey, T>): Iterator<TKey|T, T|TKey>
                 */
                static fn (callable ...$callbackForValues): Closure =>
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return Iterator<T|TKey, T|TKey>
                     */
                    static function (Iterator $iterator) use ($callbackForKeys, $callbackForValues): Iterator {
                        $callbackFactory =
                            /**
                             * @param TKey $key
                             *
                             * @return Closure(T): Closure(T|TKey, callable(T|TKey, TKey, T, Iterator<TKey, T>): (T|TKey), int, Iterator<TKey, T>): (T|TKey)
                             */
                            static fn ($key): Closure =>
                                /**
                                 * @param T $value
                                 *
                                 * @return Closure(T|TKey, callable(T|TKey, TKey, T, Iterator<TKey, T>): (T|TKey), int, Iterator<TKey, T>): (T|TKey)
                                 */
                                static fn ($value): Closure =>
                                    /**
                                     * @param T|TKey $accumulator
                                     * @param callable(T|TKey, TKey, T, Iterator<TKey, T>): (T|TKey) $callback
                                     * @param Iterator<TKey, T> $iterator
                                     *
                                     * @return T|TKey
                                     */
                                    static fn ($accumulator, callable $callback, int $callbackId, Iterator $iterator) => $callback($accumulator, $key, $value, $iterator);

                        foreach ($iterator as $key => $value) {
                            $reduceCallback = $callbackFactory($key)($value);

                            /** @var Iterator<int, T|TKey> $k */
                            $k = (new Reduce())()($reduceCallback)($key)(new ArrayIterator($callbackForKeys));

                            /** @var Iterator<int, T|TKey> $c */
                            $c = (new Reduce())()($reduceCallback)($value)(new ArrayIterator($callbackForValues));

                            yield $k->current() => $c->current();
                        }
                    };
    }
}
