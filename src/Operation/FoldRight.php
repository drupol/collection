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
final class FoldRight extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable((T|null), T, TKey, iterable<TKey, T>):(T|null)): Closure(T): Closure(iterable<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T|null, T, TKey, iterable<TKey, T>):(T|null) $callback
             *
             * @return Closure(T): Closure(iterable<TKey, T>): Generator<TKey, T>
             */
            static fn (callable $callback): Closure => static function ($initial = null) use ($callback): Closure {
                /** @var Closure(iterable<TKey, T>): Generator<TKey, T> $pipe */
                $pipe = (new Pipe())()(
                    (new ScanRight())()($callback)($initial),
                    (new Head())()
                );

                // Point free style.
                return $pipe;
            };
    }
}
