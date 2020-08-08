<?php

declare(strict_types=1);

namespace loophp\collection\Contract\Operation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 */
interface Getable
{
    /**
     * Get an item by key.
     *
     * @param int|string $key
     * @psalm-param TKey $key
     *
     * @param mixed $default
     * @psalm-param T|null $default
     *
     * @return mixed
     * @psalm-return T|null
     */
    public function get($key, $default = null);
}
