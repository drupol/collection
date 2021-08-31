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

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Permutate implements Operation
{
    /**
     * @pure
     */
    public function __invoke(): Closure
    {
        $getPermutations =
            /**
             * @param list<T> $dataset
             *
             * @return Iterator<int, list<T>>
             */
            fn (array $dataset): Iterator => $this->getPermutations($dataset);

        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<int, list<T>>
             */
            static fn (Iterator $iterator): Iterator => $getPermutations([...$iterator]);
    }

    /**
     * @param list<T> $dataset
     *
     * @return Iterator<int, list<T>>
     */
    private function getPermutations(array $dataset): Iterator
    {
        foreach ($dataset as $key => $firstItem) {
            $remaining = $dataset;

            array_splice($remaining, $key, 1);

            if ([] === $remaining) {
                yield [$firstItem];

                continue;
            }

            foreach ($this->getPermutations($remaining) as $permutation) {
                // TODO: Fix this.
                array_unshift($permutation, $firstItem);

                yield $permutation;
            }
        }
    }
}
