<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
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
final class ScanLeft extends AbstractOperation
{
    /**
     * @pure
     *
     * @template V
     * @template W
     *
     * @return Closure(callable(V|W, T, TKey, Iterator<TKey, T>): W): Closure(V): Closure(Iterator<TKey, T>): Generator<int|TKey, V|W>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(V|W, T, TKey, Iterator<TKey, T>): W $callback
             *
             * @return Closure(V): Closure(Iterator<TKey, T>): Generator<int|TKey, V|W>
             */
            static fn (callable $callback): Closure =>
                /**
                 * @param V $initial
                 *
                 * @return Closure(Iterator<TKey, T>): Generator<int|TKey, V|W>
                 */
                static function ($initial) use ($callback): Closure {
                    /** @var Closure(Iterator<TKey, T>): Generator<int|TKey, V|W> $pipe */
                    $pipe = Pipe::of()(
                        Reduction::of()($callback)($initial),
                        Prepend::of()(new ArrayIterator([$initial]))
                    );

                    return $pipe;
                };
    }
}
