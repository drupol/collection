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
final class Unzip implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, list<T>>): Iterator<int, list<T>>
     */
    public function __invoke(): Closure
    {
        $reduceCallback =
            /**
             * @param array<int, list<T>> $carry
             * @param iterable<TKey, T> $value
             *
             * @return array<int, list<T>>
             */
            static function (array $carry, iterable $value): array {
                $index = 0;

                foreach ($value as $v) {
                    $carry[$index++][] = $v;
                }

                return $carry;
            };

        // Point free style.
        return Pipe::ofTyped2(
            (new Reduce())($reduceCallback)([]),
            (new Flatten())(1)
        );
    }
}
