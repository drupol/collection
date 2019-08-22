<?php

declare(strict_types=1);

namespace drupol\collection\Operation;

use drupol\collection\Collection;
use drupol\collection\Contract\BaseCollection as CollectionInterface;

/**
 * Class Normalize.
 */
final class Normalize extends Operation
{
    /**
     * {@inheritdoc}
     */
    public function run(CollectionInterface $collection): CollectionInterface
    {
        return Collection::with(
            static function () use ($collection) {
                foreach ($collection as $item) {
                    yield $item;
                }
            }
        );
    }
}
