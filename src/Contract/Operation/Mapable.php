<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Contract\Operation;

use Iterator;
use loophp\collection\Contract\Collection;

/**
 * @template TKey
 * @template T
 * @template V
 */
interface Mapable
{
    /**
     * Apply a single callback to every item of a collection and use the return value.
     *
     * @see https://loophp-collection.readthedocs.io/en/stable/pages/api.html#map
     *
     * @param callable(T=, TKey=, Iterator<TKey, T>=): V $callback
     *
     * @return Collection<TKey, V>
     */
    public function map(callable $callback): Collection;
}
