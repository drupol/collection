<?php

declare(strict_types=1);

namespace loophp\collection\Contract\Operation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 */
interface FoldRightable
{
    /**
     * Fold the collection from the right to the left.
     *
     * @param mixed $initial
     * @psalm-param T|null $initial
     *
     * @return mixed
     * @psalm-return T|null
     */
    public function foldRight(callable $callback, $initial = null);
}
