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
use loophp\fpt\FPT;

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
                        FPT::reduction()($callback)($initial),
                        Prepend::of()($initial)
                    );

                    return $pipe;
                };
    }
}
