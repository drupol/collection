<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Iterator\MultipleIterableIterator;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 */
final class Merge extends AbstractOperation
{
    /**
     * @psalm-return Closure(iterable<TKey, T>...): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param iterable<TKey, T> ...$sources
             */
            static fn (iterable ...$sources): Closure =>
                /**
                 * @psalm-param Iterator<TKey, T> $iterator
                 *
                 * @psalm-return Iterator<TKey, T>
                 */
                static fn (Iterator $iterator): Iterator => new MultipleIterableIterator($iterator, ...$sources);
    }
}
