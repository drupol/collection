<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use Generator;
use Iterator;
use loophp\collection\Iterator\IteratorFactory;
use MultipleIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Combine extends AbstractOperation
{
    /**
     * @pure
     *
     * @template U
     *
     * @return Closure(U...): Closure(Iterator<TKey, T>): Generator<null|U, null|T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param U ...$keys
             *
             * @return Closure(Iterator<TKey, T>): Generator<null|U, null|T>
             */
            static function (...$keys): Closure {
                $pipe = Pipe::of()(
                    IteratorFactory::multipleIteratorTest()(MultipleIterator::MIT_NEED_ANY)(new ArrayIterator($keys)),
                    Unwrap::of(),
                    Pair::of(),
                );

                return $pipe;
            };
    }
}
