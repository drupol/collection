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
 * @psalm-template T
 * @psalm-template T of array-key
 */
final class Flip extends AbstractOperation
{
    /**
     * @psalm-return Closure(Iterator<TKey, T>): Generator<T, TKey>
     */
    public function __invoke(): Closure
    {
        $callbackForKeys =
            /**
             * @psalm-param mixed $carry
             * @psalm-param TKey $key
             * @psalm-param T $value
             *
             * @psalm-return T
             */
            static fn ($carry, $key, $value) => $value;

        $callbackForValues =
            /**
             * @psalm-param mixed $carry
             * @psalm-param TKey $key
             *
             * @psalm-return TKey
             */
            static fn ($carry, $key) => $key;

        /** @psalm-var Closure(Iterator<TKey, T>): Generator<T, TKey> $associate */
        $associate = Associate::of()($callbackForKeys)($callbackForValues);

        // Point free style.
        return $associate;
    }
}
