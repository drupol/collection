<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use loophp\iterators\IterableIteratorAggregate;
use loophp\iterators\LimitIterableAggregate;
use loophp\iterators\NormalizeIterableAggregate;
use loophp\iterators\ReductionIterableAggregate;

use function array_slice;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Tails extends AbstractOperation
{
    /**
     * @return Closure(iterable<array-key, T>): Generator<int, list<T>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param iterable<array-key, T> $iterable
             *
             * @return Generator<int, list<T>>
             */
            static function (iterable $iterable): Generator {
                $generator = iterator_to_array((new IterableIteratorAggregate($iterable))->getIterator());
                // We could use a value such as `false` or `0`, but it would
                // be too complex to deal with S.A. annotations.
                array_unshift($generator, current($generator));

                yield from new NormalizeIterableAggregate(new LimitIterableAggregate(new ReductionIterableAggregate(
                    $generator,
                    /**
                     * @param list<T> $stack
                     *
                     * @return list<T>
                     */
                    static fn (array $stack): array => array_slice($stack, 1),
                    $generator
                ), 1));
            };
    }
}
