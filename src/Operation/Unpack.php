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
use loophp\collection\Iterator\IterableIterator;
use loophp\fpt\FPT;

/**
 * @immutable
 *
 * @template NewTKey
 * @template NewT
 *
 * @template TKey
 * @template T of array{0: NewTKey, 1: NewT}
 */
final class Unpack extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Generator<NewTKey, NewT>
     */
    public function __invoke(): Closure
    {
        $toIterableIterator = static fn (iterable $value): Iterator => new IterableIterator($value);

        $callbackForKeys =
            /**
             * @param NewTKey $initial
             * @param T $value
             *
             * @return NewTKey
             */
            static fn ($initial, int $key, array $value) => $value[0];

        $callbackForValues =
            /**
             * @param NewT $initial
             * @param T $value
             *
             * @return NewT
             */
            static fn ($initial, int $key, array $value) => $value[1];

        /** @var Closure(Iterator<TKey, T>): Generator<NewTKey, NewT> $pipe */
        $pipe = Pipe::of()(
            Map::of()(
                static fn (iterable $value): Iterator => new IterableIterator($value)
            ),
            Map::of()(
                Chunk::of()(2)
            ),
            Unwrap::of(),
            Associate::of()(
                FPT::compose()(
                    'current',
                    FPT::arg()(2)
                )
            )(
                FPT::compose()(
                    'end',
                    FPT::arg()(2)
                )
            )
        );

        // Point free style.
        return $pipe;
    }
}
