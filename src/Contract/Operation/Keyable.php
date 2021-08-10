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
interface Keyable
{
    /**
     * Get the key of an item in the collection given a numeric index, default index is 0.
     *
     * @see https://loophp-collection.readthedocs.io/en/stable/pages/api.html#key
     *
     * @return TKey|null
     */
    public function key(int $index = 0, $default = null);
}
