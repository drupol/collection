<?php

declare(strict_types=1);

namespace drupol\collection\Contract;

/**
 * Interface Forgetable.
 */
interface Forgetable
{
    /**
     * Remove an item by key.
     *
     * @param string ...$keys
     *
     * @return \drupol\collection\Contract\BaseCollection
     */
    public function forget(...$keys): BaseCollection;
}
