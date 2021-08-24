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
 */
interface FlatMapable
{
    /**
     * Transform the collection using a callback and keep the return value, then flatten it one level.
     * The supplied callback needs to return an `iterable`: either an `array` or a class that implements Traversable.
     *
     * @see https://loophp-collection.readthedocs.io/en/stable/pages/api.html#flatmap
     *
     * @template IKey
     * @template IValue
     *
     * @param callable(T, TKey, Iterator<TKey, T>): iterable<IKey, IValue> $callback
     *
     * @return Collection<IKey, IValue>
     */
    public function flatMap(callable $callback): Collection;
}
