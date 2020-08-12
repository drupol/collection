<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Contract\EagerOperation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * @implements EagerOperation<TKey, T>
 */
final class Nullsy extends AbstractEagerOperation implements EagerOperation
{
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param \Iterator<TKey, T> $collection
             */
            static function (Iterator $collection): bool {
                foreach ($collection as $key => $value) {
                    if (null !== $value) {
                        return false;
                    }
                }

                return true;
            };
    }
}
