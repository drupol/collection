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
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Contains extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T ...$values): Closure(Iterator<TKey, T>): Generator<TKey, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T ...$values
             *
             * @return Closure(Iterator<TKey, T>): Generator<TKey, bool>
             */
            static function (...$values): Closure {
                /** @var Closure(Iterator<TKey, T>): Generator<int, bool> $matchOne */
                $matchOne = MatchOne::of()(FPT::thunk()(true))(...FPT::map()(FPT::operator()(Operator::OP_EQUAL))($values));

                // Point free style.
                return $matchOne;
            };
    }
}
