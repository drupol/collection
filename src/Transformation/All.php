<?php

declare(strict_types=1);

namespace loophp\collection\Transformation;

use Iterator;
use loophp\collection\Contract\Transformation;
use loophp\collection\Transformation\AbstractTransformation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * @implements Transformation<TKey, T>
 */
final class All extends AbstractTransformation implements Transformation
{
    public function __invoke()
    {
        return static function (Iterator $collection): array {
            return iterator_to_array($collection);
        };
    }
}
