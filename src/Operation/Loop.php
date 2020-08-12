<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use InfiniteIterator;
use Iterator;
use loophp\collection\Contract\LazyOperation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * @implements LazyOperation<TKey, T>
 */
final class Loop extends AbstractLazyOperation implements LazyOperation
{
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param \Iterator<TKey, T> $iterator
             *
             * @psalm-return \Generator<TKey, T>
             */
            static function (Iterator $iterator): Generator {
                return yield from new InfiniteIterator($iterator);
            };
    }
}
