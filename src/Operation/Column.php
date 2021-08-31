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
final class Column implements Operation
{
    /**
     * @pure
     *
     * @param mixed $column
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int, mixed>
     */
    public function __invoke($column): Closure
    {
        $filterCallbackBuilder =
            /**
             * @param mixed $column
             */
            static fn ($column): Closure =>
                /**
                 * @param T $value
                 * @param TKey $key
                 */
                static fn ($value, $key): bool => $key === $column;

        $pipe = Pipe::ofTyped4(
            (new Transpose())(),
            (new Filter())($filterCallbackBuilder($column)),
            (new Head()),
            (new Flatten())(1)
        );

        // Point free style.
        return $pipe;
    }
}
