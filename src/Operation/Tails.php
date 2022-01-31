<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;

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
     * @return Closure(iterable<TKey, T>): Generator<int, list<T>, mixed, void>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param iterable<TKey, T> $iterable
             *
             * @return Generator<int, list<T>, mixed, void>
             */
            static function (iterable $iterable): Generator {
                /** @var Generator<int, array{0: TKey, 1: T}> $generator */
                $generator = Pack::of()($iterable);
                $data = [...$generator];

                while ([] !== $data) {
                    yield [...Unpack::of()($data)];

                    array_shift($data);
                }

                yield [];
            };
    }
}
