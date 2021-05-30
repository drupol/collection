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

use function array_slice;

/**
 * @template TKey
 * @template T
 */
final class Window extends AbstractOperation
{
    /**
     * @return Closure(int): Closure(Iterator<TKey, T>): Generator<TKey, T|list<T>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(Iterator<TKey, T>): Generator<TKey, T|list<T>>
             */
            static fn (int $size): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Generator<TKey, list<T>|T>
                 */
                static function (Iterator $iterator) use ($size): Generator {
                    if (0 === $size) {
                        return yield from $iterator;
                    }

                    ++$size;
                    $size *= -1;

                    $stack = [];

                    foreach ($iterator as $key => $current) {
                        yield $key => $stack = array_slice([...$stack, $current], $size);
                    }
                };
    }
}
