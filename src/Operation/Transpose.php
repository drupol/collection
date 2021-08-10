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
use loophp\collection\Iterator\IterableIterator;
use loophp\fpt\FPT;
use MultipleIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Transpose extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Generator<TKey, list<T>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Generator<TKey, list<T>>
             */
            static function (Iterator $iterator): Generator {
                $mit = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);

                foreach ($iterator as $iteratorIterator) {
                    $mit->attachIterator(new IterableIterator($iteratorIterator));
                }

                /** @var Generator<TKey, list<T>> $associate */
                $associate = Associate::of()(
                    static fn ($value, $key): mixed => current($key)
                )(
                    FPT::arg()(2)
                )($mit);

                // Point free style.
                return $associate;
            };
    }
}
