<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Contract\Operation;
use loophp\collection\Iterator\IterableIterator;
use MultipleIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Transpose implements Operation
{
    /**
     * @pure
     *
     * @psalm-suppress ImpureMethodCall - using MultipleIterator as an internal tool which is not returned
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, list<T>>
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

        return Pipe::ofTyped3(
            (new Reduce())(
                static function (MultipleIterator $acc, iterable $iterable): MultipleIterator {
                    $acc->attachIterator(new IterableIterator($iterable));

                    return $acc;
                }
            )(new MultipleIterator(MultipleIterator::MIT_NEED_ANY)),
            (new Flatten())(1),
            (new Associate())($callbackForKeys)($callbackForValues)
        );
    }
}
