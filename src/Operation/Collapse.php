<?php

declare(strict_types=1);

namespace drupol\collection\Operation;

use drupol\collection\Collection;
use drupol\collection\Contract\BaseCollection as BaseCollectionInterface;

/**
 * Class Collapse.
 */
final class Collapse extends Operation
{
    /**
     * {@inheritdoc}
     */
    public function run(BaseCollectionInterface $collection): BaseCollectionInterface
    {
        return $collection::with(
            static function () use ($collection): \Generator {
                foreach ($collection as $item) {
                    if (\is_array($item) || $item instanceof Collection) {
                        foreach ($item as $value) {
                            yield $value;
                        }
                    }
                }
            }
        );
    }
}
