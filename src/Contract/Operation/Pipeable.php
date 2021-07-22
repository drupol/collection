<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Contract\Operation;

use loophp\collection\Contract\Collection;

/**
 * @template TKey
 * @template T
 */
interface Pipeable
{
    /**
     * Pipe together multiple operations and apply them in succession to the collection items.
     * To maintain a lazy nature, each operation needs to return a `Generator`.
     * Custom operations and operations provided in the API can be combined together.
     *
     * @see https://loophp-collection.readthedocs.io/en/stable/pages/api.html#pipe
     *
     * @param callable(iterable<TKey, T>): iterable<TKey, T> ...$callbacks
     *
     * @return Collection<TKey, T>
     */
    public function pipe(callable ...$callbacks): Collection;
}
