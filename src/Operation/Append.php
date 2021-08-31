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
use loophp\collection\Iterator\MultipleIterableIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Append implements Operation
{
    /**
     * @pure
     *
     * @param T ...$items
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int|TKey, T>
     */
    public function __invoke(...$items): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<int|TKey, T>
             */
            static fn (Iterator $iterator): Iterator => new MultipleIterableIterator($iterator, $items);
    }
}
