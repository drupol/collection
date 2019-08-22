<?php

declare(strict_types=1);

namespace drupol\collection\Operation;

use drupol\collection\Contract\BaseCollection as BaseCollectionInterface;

/**
 * Class First.
 */
final class First extends Operation
{
    /**
     * {@inheritdoc}
     */
    public function run(BaseCollectionInterface $collection)
    {
        [$callback, $default] = $this->parameters;

        if (null === $callback) {
            $callback = static function ($v, $k) {
                return true;
            };
        }

        foreach ($collection as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }
}
