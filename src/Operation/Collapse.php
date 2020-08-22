<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;
use loophp\collection\Contract\LazyOperation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * @implements LazyOperation<TKey, T>
 */
final class Collapse extends AbstractLazyOperation implements LazyOperation
{
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param \Iterator<array-key, T|iterable<TKey, T>> $iterator
             *
             * @psalm-return \Generator<TKey, T>
             */
            static function (Iterator $iterator): Generator {
                foreach ($iterator as $value) {
                    if (false === is_iterable($value)) {
                        continue;
                    }

                    /**
                     * @var TKey $subKey
                     * @var T $subValue
                     */
                    foreach ($value as $subKey => $subValue) {
                        yield $subKey => $subValue;
                    }
                }
            };
    }
}
