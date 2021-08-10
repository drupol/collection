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
final class Has extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(T, TKey, Iterator<TKey, T>): T ...): Closure(Iterator<TKey, T>): Generator<TKey, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T, TKey, Iterator<TKey, T>): T ...$callbacks
             *
             * @return Closure(Iterator<TKey, T>): Generator<TKey, bool>
             */
            static function (callable ...$callbacks): Closure {
                /** @var Closure(Iterator<TKey, T>): Generator<int|TKey, bool> $pipe */
                $pipe = MatchOne::of()(FPT::thunk()(true))(...FPT::map()(static fn (callable $callback): callable => static fn ($value, $key, Iterator $iterator): bool => FPT::operator()(Operator::OP_EQUAL)($value)($callback($value, $key, $iterator)))($callbacks));

                // Point free style.
                return $pipe;
            };
    }
}
