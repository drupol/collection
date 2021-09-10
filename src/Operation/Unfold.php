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
final class Unfold extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T...): Closure(callable(mixed|T...): (mixed|array<TKey, T>)): Closure(): Iterator<int, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T ...$parameters
             *
             * @return Closure(callable(mixed|T...): (array<TKey, T>)): Closure(): Iterator<int, T>
             */
            static fn (...$parameters): Closure =>
                /**
                 * @param callable(mixed|T...): (mixed|array<TKey, T>) $callback
                 *
                 * @return Closure(): Iterator<int, T>
                 */
                static fn (callable $callback): Closure =>
                    /**
                     * @return Iterator<int, T>
                     */
                    static function () use ($parameters, $callback): Iterator {
                        while (true) {
                            /** @var T $parameters */
                            $parameters = $callback(...array_values((array) $parameters));

                            yield $parameters;
                        }
                    };
    }
}
