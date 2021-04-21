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
final class Truthy extends AbstractOperation
{
    /**
     * @psalm-return Closure(Iterator<TKey, T>): Generator<int, bool>
     */
    public function __invoke(): Closure
    {
        $callback =
            /**
             * @param mixed $value
             * @psalm-param T $value
             */
            static fn ($value): bool => !(bool) $value;

        /** @psalm-var Closure(Iterator<TKey, T>): Generator<int, bool> $pipe */
        $pipe = Pipe::of()(
            MatchOne::of()(static fn (): bool => true)($callback),
            Map::of()($callback),
        );

        // Point free style.
        return $pipe;
    }
}
