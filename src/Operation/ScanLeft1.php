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

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class ScanLeft1 extends AbstractOperation
{
    /**
     * @pure
     *
     * @template V
     *
     * @return Closure(callable(mixed, T, TKey, Iterator<TKey, T>): mixed): Closure(Iterator<TKey, T>): Generator<int|TKey, mixed>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T|V, T, TKey, Iterator<TKey, T>): V $callback
             *
             * @return Closure(Iterator<TKey, T>): Generator<int|TKey, T|V>
             */
            static fn (callable $callback): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Generator<int|TKey, T|V>
                 */
                static function (Iterator $iterator) use ($callback): Iterator {
                    $initial = $iterator->current();

                    /** @var Closure(Iterator<TKey, T>): Generator<int|TKey, T|V> $pipe */
                    $pipe = (new Pipe())()(
                        (new Tail())(),
                        (new Reduction())()($callback)($initial),
                        (new Prepend())()($initial)
                    );

                    return $pipe($iterator);
                };
    }
}
