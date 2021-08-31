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
final class Merge implements Operation
{
    /**
     * @pure
     *
     * @param iterable<TKey, T> ...$sources
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(iterable ...$sources): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<TKey, T>
             */
            static fn (Iterator $iterator): Iterator => new MultipleIterableIterator($iterator, ...$sources);
    }
}
