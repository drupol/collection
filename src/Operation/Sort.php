<?php

declare(strict_types=1);

namespace drupol\collection\Operation;

use drupol\collection\Collection;
use drupol\collection\Contract\BaseCollection as BaseCollectionInterface;

/**
 * Class Sort.
 *
 * Be careful, this will only work with finite collection sets.
 */
final class Sort extends Operation
{
    /**
     * Sort constructor.
     *
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        parent::__construct(...[$callback]);
    }

    /**
     * {@inheritdoc}
     */
    public function run(BaseCollectionInterface $collection): BaseCollectionInterface
    {
        [$callback] = $this->parameters;

        return $collection::with(
            static function () use ($callback, $collection): \Generator {
                $array = \iterator_to_array($collection->getIterator());

                \uasort($array, $callback);

                yield from $array;
            }
        );
    }
}
