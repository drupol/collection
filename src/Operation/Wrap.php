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
final class Wrap implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int, array<TKey, T>>
     */
    public function __invoke(): Closure
    {
        $mapCallback =
            /**
             * @param T $value
             * @param TKey $key
             *
             * @return array<TKey, T>
             */
            static fn ($value, $key): array => [$key => $value];

        return Pipe::ofTyped2(
            (new Map())($mapCallback),
            (new Normalize())
        );
    }
}
