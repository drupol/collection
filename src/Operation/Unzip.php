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

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Unzip extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, list<T>>): Generator<int, list<T>>
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

        /** @var Closure(Iterator<TKey, list<T>>): Generator<int, list<T>> $pipe */
        $pipe = Pipe::of()(
            Reduce::of()($reduceCallback)([]),
            Flatten::of()(1)
        );

        // Point free style.
        return $pipe;
    }
}
