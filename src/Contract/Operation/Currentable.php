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
interface Currentable
{
    /**
     * Get the value of an item in the collection given a numeric index or the default `0`.
     *
     * @see https://loophp-collection.readthedocs.io/en/stable/pages/api.html#current
     *
     * @return T|null
     */
    public function current(int $index = 0, $default = null);
}
