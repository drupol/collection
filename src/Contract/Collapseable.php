<?php

declare(strict_types=1);

namespace drupol\collection\Contract;

/**
 * Interface Collapseable.
 */
interface Collapseable
{
    /**
     * Collapse the collection of items into a single array.
     *
     * @return \drupol\collection\Contract\BaseCollection
     */
    public function collapse(): BaseCollection;
}
