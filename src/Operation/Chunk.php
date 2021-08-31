<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use EmptyIterator;
use Iterator;
use loophp\collection\Contract\Operation;

use function count;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Chunk implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int, list<T>>
     */
    public function __invoke(int ...$sizes): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<int, list<T>>
             */
            static function (Iterator $iterator) use ($sizes): Iterator {
                $sizesIterator = (new Cycle())(new ArrayIterator($sizes));
                $sizesIterator->rewind();

                $values = [];

                foreach ($iterator as $value) {
                    $size = $sizesIterator->current();

                    if (0 >= $size) {
                        return new EmptyIterator();
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
