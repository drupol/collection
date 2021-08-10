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

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Falsy extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Generator<int, bool>
     */
    public function __invoke(): Closure
    {
        /** @var callable(T, TKey, Iterator<TKey, T>): bool $boolVal */
        $boolVal = FPT::curry()('boolval');

        /** @var callable(T, TKey, Iterator<TKey, T>): bool $notBoolVal */
        $notBoolVal = FPT::not()($boolVal);

        /** @var Closure(Iterator<TKey, T>): Generator<int, bool> $pipe */
        $pipe = Pipe::of()(
            MatchOne::of()(FPT::thunk()(true))($boolVal),
            Map::of()($notBoolVal),
        );

        // Point free style.
        return $pipe;
    }
}
