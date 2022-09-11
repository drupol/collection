<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Contract\Operation;

use Iterator;

/**
 * @template TKey
 * @template T
 */
interface FoldRight1able
{
    /**
     * @param callable(T, T, TKey, Iterator<TKey, T>): T $callback
     *
     * @return T|null
     */
    public function foldRight1(callable $callback);
}
