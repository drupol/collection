<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @template TKey
 * @psalm-template TKey of array-key
 * @template T
 * @extends AbstractOperation<TKey, T, Generator<int, TKey>>
 * @implements Operation<TKey, T, Generator<int, TKey>>
 */
final class Keys extends AbstractOperation implements Operation
{
    /**
     * @return Closure(\Iterator<TKey, T>): Generator<int, TKey>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Generator<int, TKey>
             */
            static function (Iterator $iterator): Generator {
                foreach ($iterator as $key => $value) {
                    yield $key;
                }
            };
    }
}
