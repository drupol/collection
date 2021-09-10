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
 */
final class Pack extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int, array{0: TKey, 1: T}>
     */
    public function __invoke(): Closure
    {
        $mapCallback =
            /**
             * @param T $value
             * @param TKey $key
             *
             * @return array{0: TKey, 1: T}
             */
            static fn ($value, $key): array => [$key, $value];

        // Point free style.
        return Pipe::ofTyped2(
            (new Map())()($mapCallback),
            (new Normalize())()
        );
    }
}
