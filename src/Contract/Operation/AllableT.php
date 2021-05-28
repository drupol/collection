<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Contract\Operation;

/**
 * @template TKey
 * @template T
 */
interface AllableT
{
    /**
     * Get all items from the collection.
     *
     * @psalm-return array<TKey, T>
     */
    public function all(): array;
}
