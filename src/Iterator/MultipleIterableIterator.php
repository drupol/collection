<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Iterator;

use AppendIterator;
use NoRewindIterator;

/**
 * @internal
 *
 * @template TKey
 * @template T
 *
 * @extends ProxyIterator<TKey, T>
 */
final class MultipleIterableIterator extends ProxyIterator
{
    /**
     * @param iterable<TKey, T> $iterators
     */
    public function __construct(iterable ...$iterators)
    {
        $appendIterator = new AppendIterator();

        foreach ($iterators as $iterator) {
            $appendIterator->append(new NoRewindIterator(new IterableIterator($iterator)));
        }

        $this->iterator = $appendIterator;
    }
}
