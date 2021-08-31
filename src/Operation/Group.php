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
final class Group implements Operation
{
    /**
     * @pure
     *
     * @param Iterator<TKey, T> $iterator
     *
     * @return Iterator<int, list<T>>
     */
    public function __invoke(Iterator $iterator): Iterator
    {
        $last = [];

        foreach ($iterator as $current) {
            if ([] === $last) {
                $last = [$current];

                continue;
            }

            if (current($last) === $current) {
                $last[] = $current;

                continue;
            }

            yield $last;

            $last = [$current];
        }

        if ([] !== $last) {
            yield $last;
        }
    }
}
