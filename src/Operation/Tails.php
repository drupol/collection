<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Tails extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int, list<T>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<int, list<T>>
             */
            static function (Iterator $iterator): Iterator {
                /** @var Iterator<int, array{0: TKey, 1: T}> $iterator */
                $iterator = (new Pack())()($iterator);
                $data = [...$iterator];

                while ([] !== $data) {
                    /** @var Iterator<TKey, T> $unpack */
                    $unpack = (new Unpack())()(new ArrayIterator($data));

                    yield [...$unpack];

                    array_shift($data);
                }

                return yield [];
            };
    }
}
