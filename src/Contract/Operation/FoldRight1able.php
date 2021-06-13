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
interface FoldRight1able
{
    /**
     * @param callable(T, T, TKey, Iterator<TKey, T>): T $callback
     *
     * @return Collection<TKey, T|null>
     */
    public function foldRight1(callable $callback): Collection;
}
