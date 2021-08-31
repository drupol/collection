<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class MapN implements Operation
{
    /**
     * @pure
     *
     * @return Closure(callable(mixed, mixed, Iterator<TKey, T>): mixed ...): Closure(Iterator<TKey, T>): Iterator<mixed, mixed>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(mixed, mixed, Iterator<TKey, T>): mixed ...$callbacks
             */
            static fn (callable ...$callbacks): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<mixed, mixed>
                 */
                static function (Iterator $iterator) use ($callbacks): Iterator {
                    $callbackFactory =
                        /**
                         * @param mixed $key
                         *
                         * @return Closure(mixed, callable(mixed, mixed, Iterator<TKey, T>): mixed): mixed
                         */
                        static fn ($key): Closure =>
                            /**
                             * @param mixed $carry
                             * @param callable(mixed, mixed, Iterator<TKey, T>): mixed $callback
                             *
                             * @return mixed
                             */
                            static fn ($carry, callable $callback) => $callback($carry, $key, $iterator);

                    foreach ($iterator as $key => $value) {
                        yield $key => array_reduce($callbacks, $callbackFactory($key), $value);
                    }
                };
    }
}
