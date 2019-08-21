<?php

declare(strict_types=1);

namespace drupol\collection\Operation;

use drupol\collection\Collection;

/**
 * Class Flatten.
 */
final class Flatten extends Operation
{
    /**
     * {@inheritdoc}
     */
    public function run(\IteratorAggregate $collection): \IteratorAggregate
    {
        $depth = $this->parameters[0];

        return Collection::with(
            static function () use ($depth, $collection): \Generator {
                $iterator = $collection->getIterator();

                foreach ($iterator as $item) {
                    if (!\is_array($item) && !$item instanceof Collection) {
                        yield $item;
                    } elseif (1 === $depth) {
                        foreach ($item as $i) {
                            yield $i;
                        }
                    } else {
                        foreach (Collection::with($item)->flatten($depth - 1) as $flattenItem) {
                            yield $flattenItem;
                        }
                    }
                }
            }
        );
    }
}
