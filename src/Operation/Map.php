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

use function count;

use const E_USER_DEPRECATED;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Map extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(T, TKey, Iterator<TKey, T>): T ...): Closure(Iterator<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T, TKey, Iterator<TKey, T>): T ...$callbacks
             */
            static fn (callable ...$callbacks): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Generator<TKey, T>
                 */
                static function (Iterator $iterator) use ($callbacks): Generator {
                    if (count($callbacks) > 1) {
                        @trigger_error(
                            'Using `Map` with multiple callbacks is deprecated, and will be removed in a future major version; use `MapN` instead.',
                            E_USER_DEPRECATED
                        );
                    }

                    $callbackFactory =
                        /**
                         * @param TKey $key
                         *
                         * @return Closure(T, callable(T, TKey, Iterator<TKey, T>): T): T
                         */
                        static fn ($key): Closure =>
                            /**
                             * @param T $carry
                             * @param callable(T, TKey, Iterator<TKey, T>): T $callback
                             *
                             * @return T
                             */
                            static fn ($carry, callable $callback) => $callback($carry, $key, $iterator);

                    foreach ($iterator as $key => $value) {
                        yield $key => array_reduce($callbacks, $callbackFactory($key), $value);
                    }
                };
    }
}
