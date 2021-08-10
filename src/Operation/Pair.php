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
 */
final class Pair extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Generator<T, T|null>
     */
    public function __invoke(): Closure
    {
        $callbackForKeys =
            /**
             * @param T $initial
             * @param TKey $key
             * @param array{0: TKey, 1: T} $value
             *
             * @return TKey|null
             */
            static fn ($initial, $key, array $value) => $value[0] ?? null;

        $callbackForValues =
            /**
             * @param T $initial
             * @param TKey $key
             * @param array{0: TKey, 1: T} $value
             *
             * @return T|null
             */
            static fn ($initial, $key, array $value) => $value[1] ?? null;

        /** @var Closure(Iterator<TKey, T>): Generator<T, T|null> $pipe */
        $pipe = Pipe::of()(
            Chunk::of()(2),
            Map::of()(FPT::curry()('array_values')),
            Associate::of()($callbackForKeys)($callbackForValues)
        );

        // Point free style.
        return $pipe;
    }
}
