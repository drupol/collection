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
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 */
final class Collapse extends AbstractOperation
{
    /**
     * @psalm-return Closure(Iterator<TKey, (T|iterable<TKey, T>)>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param Iterator<TKey, T|iterable<TKey, T>> $iterator
             *
             * @psalm-return Generator<TKey, T>
             */
            static function (Iterator $iterator): Generator {
                /** @psalm-var Closure(Iterator<TKey, T|iterable<TKey, T>>): Generator<TKey, iterable<TKey, T>> $filter */
                $filter = Filter::of()(
                    /**
                     * @param mixed $value
                     * @psalm-param T $value
                     */
                    static fn ($value): bool => is_iterable($value)
                );

                foreach ($filter($iterator) as $value) {
                    yield from $value;
                }
            };
    }
}
