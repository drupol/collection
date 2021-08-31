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
 */
final class Pair implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<T, T|null>
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

        // Point free style.
        return Pipe::ofTyped3(
            (new Chunk())(2),
            (new Map())(static fn (array $value): array => array_values($value)),
            (new Associate())($callbackForKeys)($callbackForValues)
        );
    }
}
