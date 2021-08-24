<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;
use loophp\collection\Iterator\IteratorFactory;
use MultipleIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Transpose extends AbstractOperation
{
    /**
     * @pure
     *
     * @psalm-suppress ImpureMethodCall - using MultipleIterator as an internal tool which is not returned
     *
     * @return Closure(Iterator<TKey, T>): Generator<TKey, list<T>>
     */
    public function __invoke(): Closure
    {
        $callbackForKeys =
            /**
             * @param array $carry
             * @param non-empty-array<int, TKey> $key
             *
             * @return TKey
             */
            static fn (array $carry, array $key) => reset($key);

        $callbackForValues =
            /**
             * @param array $carry
             * @param array<int, TKey> $key
             * @param array<int, T> $value
             *
             * @return array<int, T>
             */
            static fn (array $carry, array $key, array $value): array => $value;

        /** @var Generator<TKey, list<T>> $associate */
        $associate = Pipe::of()(
            Map::of()(IteratorFactory::iterableIterator()),
            IteratorFactory::multipleIterators()(MultipleIterator::MIT_NEED_ANY),
            Associate::of()($callbackForKeys)($callbackForValues),
        );

        return $associate;
    }
}
