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
interface AsyncMapNable
{
    /**
     * Asynchronously apply callbacks to every item of a collection and use the return value.
     *
     * @param callable(mixed, mixed): mixed ...$callbacks
     *
     * @return Collection<mixed, mixed>
     */
    public function asyncMapN(callable ...$callbacks): Collection;
}
