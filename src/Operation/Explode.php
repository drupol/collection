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
use loophp\collection\Contract\Operation\Splitable;
use loophp\fpt\FPT;
use loophp\fpt\Operator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Explode extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T...): Closure(Iterator<TKey, T>): Generator<int, list<T>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T ...$explodes
             *
             * @return Closure(Iterator<TKey, T>): Generator<int, list<T>>
             */
            static function (...$explodes): Closure {
                /** @var Closure(Iterator<TKey, T>): Generator<int, list<T>> $split */
                $split = Split::of()(Splitable::REMOVE)(...FPT::map()(FPT::operator()(Operator::OP_EQUAL))($explodes));

                // Point free style.
                return $split;
            };
    }
}
