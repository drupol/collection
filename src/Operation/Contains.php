<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Contains implements Operation
{
    /**
     * @pure
     *
     * @param T ...$values
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, bool>
     */
    public function __invoke(...$values): Closure
    {
        $callback =
            /**
             * @param T $left
             */
            static fn ($left): Closure =>
                /**
                 * @param T $right
                 */
                static fn ($right): bool => $left === $right;

        $matchOne = (new MatchOne())(static fn (): bool => true)(...array_map($callback, $values));

        // Point free style.
        return $matchOne;
    }
}
