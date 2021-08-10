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
use loophp\fpt\FPT;
use loophp\fpt\Operator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Get extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(TKey): Closure (T|null): Closure(Iterator<TKey, T>): Generator<TKey, T|null>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param TKey $keyToGet
             *
             * @return Closure(T|null): Closure(Iterator<TKey, T>): Generator<TKey, T|null>
             */
            static fn ($keyToGet): Closure =>
                /**
                 * @param T|null $default
                 *
                 * @return Closure(Iterator<TKey, T>): Generator<TKey, T|null>
                 */
                static function ($default) use ($keyToGet): Closure {
                    /** @var Closure(Iterator<TKey, T>):(Generator<int|TKey, T>) $pipe */
                    $pipe = Pipe::of()(
                        Filter::of()(
                            FPT::compose()(
                                FPT::operator()(Operator::OP_EQUAL)($keyToGet),
                                FPT::arg()(1)
                            )
                        ),
                        Append::of()($default),
                        Head::of()
                    );

                    // Point free style.
                    return $pipe;
                };
    }
}
