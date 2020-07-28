<?php

declare(strict_types=1);

namespace loophp\collection\Contract\Operation;

use loophp\collection\Contract\Collection;

/**
 * @template TKey
 * @psalm-template TKey of array-key
 * @template T
 */
interface Untilable
{
    /**
     * @param callable(T, TKey): (bool) ...$callbacks
     *
     * @return Collection<TKey, T>
     */
    public function until(callable ...$callbacks): Collection;
}
