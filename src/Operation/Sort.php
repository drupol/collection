<?php

declare(strict_types=1);

namespace drupol\collection\Operation;

use drupol\collection\Collection;
use drupol\collection\Contract\BaseCollection as CollectionInterface;

/**
 * Class Sort.
 *
 * Be careful, this will only work with finite collection sets.
 */
final class Sort extends Operation
{
    /**
     * {@inheritdoc}
     */
    public function run(CollectionInterface $collection): CollectionInterface
    {
        [$callback] = $this->parameters;

        return Collection::with(
            static function () use ($callback, $collection): \Generator {
                $array = \iterator_to_array($collection->getIterator());

                \uasort($array, $callback);

                yield from $array;
            }
        );
    }
}
