<?php

declare(strict_types=1);

namespace loophp\collection\Contract\Operation;

use loophp\collection\Contract\Collection;

/**
 * @template TKey
 * @psalm-template TKey of array-key
 * @template T
 */
interface Forgetable
{
    /**
     * Remove items having specific keys.
     *
     * @param TKey ...$keys
     *
     * @return Collection<TKey, T>
     */
    public function forget(...$keys): Collection;
}
