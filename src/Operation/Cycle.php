<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use InfiniteIterator;
use Iterator;
use LimitIterator;
use loophp\collection\Contract\Operation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 */
final class Cycle extends AbstractOperation implements Operation
{
    public function __construct(int $length)
    {
        $this->storage['length'] = $length;
    }

    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param Iterator<TKey, T> $iterator
             *
             * @psalm-return Generator<TKey, T>
             */
            static function (Iterator $iterator, int $length): Generator {
                if (0 === $length) {
                    return yield from [];
                }

                return yield from new LimitIterator(
                    new InfiniteIterator($iterator),
                    0,
                    $length
                );
            };
    }
}
