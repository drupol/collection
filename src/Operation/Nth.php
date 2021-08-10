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
final class Nth extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int): Closure(int): Closure(Iterator<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(int): Closure(Iterator<TKey, T>): Generator<TKey, T>
             */
            static fn (int $step): Closure =>
                /**
                 * @return Closure(Iterator<TKey, T>): Generator<TKey, T>
                 */
                static function (int $offset) use ($step): Closure {
                    /** @var Closure(Iterator<TKey, T>): Generator<TKey, T> $pipe */
                    $pipe = Filter::of()(
                        FPT::compose()(
                            FPT::operator()(Operator::OP_EQUAL)($offset),
                            // TODO: Unable to use FPT for this yet(to make it point free)
                            static fn ($value, $key): int => $key % $step
                        )
                    );

                    // Point free style.
                    return $pipe;
                };
    }
}
