<?php

declare(strict_types=1);

namespace drupol\collection\Operation;

use drupol\collection\Contract\BaseCollection as BaseCollectionInterface;

/**
 * Class Limit.
 */
final class Limit extends Operation
{
    /**
     * Limit constructor.
     *
     * @param int $limit
     */
    public function __construct(int $limit)
    {
        parent::__construct(...[$limit]);
    }

    /**
     * {@inheritdoc}
     */
    public function run(BaseCollectionInterface $collection): BaseCollectionInterface
    {
        [$limit] = $this->parameters;

        return $collection::with(
            static function () use ($limit, $collection): \Generator {
                $i = 0;

                foreach ($collection as $key => $value) {
                    yield $key => $value;

                    if (++$i === $limit) {
                        break;
                    }
                }
            }
        );
    }
}
