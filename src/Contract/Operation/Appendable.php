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
interface Appendable
{
    /**
     * Add one or more items to a collection.
     *
     * @param mixed ...$items
     *
     * @psalm-return Collection<TKey|int, mixed>
     */
    public function append(...$items): Collection;
}
