<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;

use function array_slice;
use function count;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Combinate extends AbstractOperation
{
    /**
     * @pure
     */
    public function __invoke(): Closure
    {
        return static function (?int $length = null): Closure {
            $getCombinations =
                /**
                 * @param array<int, T> $dataset
                 *
                 * @return Iterator<array<int, T>>
                 */
                static function (array $dataset, int $length) use (&$getCombinations): Iterator {
                    for ($i = 0; count($dataset) - $length >= $i; ++$i) {
                        if (1 === $length) {
                            yield [$dataset[$i]];

                            continue;
                        }

                        /** @var array<int, T> $permutation */
                        foreach ($getCombinations(array_slice($dataset, $i + 1), $length - 1) as $permutation) {
                            array_unshift($permutation, $dataset[$i]);

                            yield $permutation;
                        }
                    }
                };

            return
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<int, array<int, T>>
                 */
                static function (Iterator $iterator) use ($length, $getCombinations): Iterator {
                    $dataset = [...$iterator];

                    if (0 < $length) {
                        return yield from $getCombinations($dataset, $length);
                    }

                    $collectionSize = count($dataset);

                    if (0 === $length) {
                        return yield from $getCombinations($dataset, $collectionSize);
                    }

                    for ($i = 1; $i <= $collectionSize; ++$i) {
                        yield from $getCombinations($dataset, $i);
                    }
                };
        };
    }
}
