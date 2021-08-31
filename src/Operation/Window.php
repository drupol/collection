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

use function array_slice;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Window implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, list<T>>
     */
    public function __invoke(int $size): Closure
    {
        // Point free style.
        return (new Reduction())(
            /**
             * @param list<T> $stack
             * @param T $current
             *
             * @return list<T>
             */
            static fn (array $stack, $current): array => array_slice([...$stack, $current], ++$size * -1)
        )([]);
    }
}
