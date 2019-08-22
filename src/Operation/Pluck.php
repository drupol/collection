<?php

declare(strict_types=1);

namespace drupol\collection\Operation;

use ArrayAccess;
use drupol\collection\Collection;
use drupol\collection\Contract\BaseCollection;
use drupol\collection\Contract\BaseCollection as BaseCollectionInterface;

/**
 * Class Pluck.
 */
final class Pluck extends Operation
{
    /**
     * Pluck constructor.
     *
     * @param array|string $key
     * @param mixed $default
     */
    public function __construct($key, $default)
    {
        parent::__construct(...[$key, $default]);
    }

    /**
     * {@inheritdoc}
     */
    public function run(BaseCollectionInterface $collection): BaseCollectionInterface
    {
        [$key, $default] = $this->parameters;
        $operation = $this;

        return $collection::with(
            static function () use ($key, $default, $collection, $operation) {
                $key = \is_string($key) ? \explode('.', \trim($key, '.')) : $key;

                foreach ($collection as $item) {
                    yield $operation->pick($collection, $item, $key, $default);
                }
            }
        );
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param \drupol\collection\Contract\BaseCollection $collection
     * @param mixed $target
     * @param array $key
     * @param mixed $default
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    private function pick($collection, $target, array $key, $default = null)
    {
        while (null !== $segment = \array_shift($key)) {
            if ('*' === $segment) {
                if (!\is_array($target)) {
                    return $default;
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = $this->pick($collection, $item, $key);
                }

                return \in_array('*', $key, true) ? (new Collapse())->run($collection::with($result)) : $result;
            }

            if ((true === \is_array($target)) && (true === \array_key_exists($segment, $target))) {
                $target = $target[$segment];
            } elseif (($target instanceof ArrayAccess) && (true === $target->offsetExists($segment))) {
                $target = $target[$segment];
            } elseif ($target instanceof BaseCollection) {
                $target = (new Get($segment, $default))->run($target);
            } elseif ((true === \is_object($target)) && (true === \property_exists($target, $segment))) {
                $target = (new \ReflectionClass($target))->getProperty($segment)->getValue($target);
            } else {
                $target = $default;
            }
        }

        return $target;
    }
}
