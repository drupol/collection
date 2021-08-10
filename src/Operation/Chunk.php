<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use Generator;
use Iterator;

use function count;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Chunk extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int...): Closure(Iterator<TKey, T>): Generator<int, list<T>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(Iterator<TKey, T>): Generator<int, list<T>>
             */
            static fn (int ...$sizes): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Generator<int, list<T>>
                 */
                static function (Iterator $iterator) use ($sizes): Generator {
                    /** @var Iterator<int, int> $sizesIterator */
                    $sizesIterator = Cycle::of()(new ArrayIterator($sizes));
                    $sizesIterator->rewind();

                    $values = [];

                    foreach ($iterator as $value) {
                        $size = $sizesIterator->current();

                        if (0 >= $size) {
                            return yield from [];
                        }

                        if (count($values) !== $size) {
                            $values[] = $value;

                            continue;
                        }

                        $sizesIterator->next();

                        yield $values;

                        $values = [$value];
                    }

                    return yield $values;
                };
    }
}
