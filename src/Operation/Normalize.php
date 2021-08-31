<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Normalize implements Operation
{
    /**
     * @pure
     *
     * @param Iterator<TKey, T> $iterator
     *
     * @return Iterator<int, T>
     */
    public function __invoke(Iterator $iterator): Iterator
    {
        foreach ($iterator as $value) {
            yield $value;
        }
    }
}
