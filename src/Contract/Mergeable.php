<?php

declare(strict_types=1);

namespace drupol\collection\Contract;

/**
 * Interface Mergeable.
 */
interface Mergeable
{
    /**
     * Push all of the given items onto the collection.
     *
     * @param iterable ...$sources
     *
     * @return \drupol\collection\Contract\BaseCollection
     */
    public function merge(...$sources): BaseCollection;
}
