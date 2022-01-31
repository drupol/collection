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
final class FlatMap extends AbstractOperation
{
    /**
     * @pure
     *
     * @template IKey
     * @template IValue
     *
     * @return Closure(callable(T=, TKey=, iterable<TKey, T>=): iterable<mixed, mixed>): Closure(iterable<TKey, T>): Generator<mixed, mixed>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T=, TKey=, iterable<TKey, T>=): iterable<mixed, mixed> $callback
             */
            static function (callable $callback): Closure {
                /** @var Closure(iterable<TKey, T>): Generator<IKey, IValue> $flatMap */
                $flatMap = (new Pipe())()(
                    (new Map())()($callback),
                    (new Flatten())()(1),
                );

                // Point free style
                return $flatMap;
            };
    }
}
