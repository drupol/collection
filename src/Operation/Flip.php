<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Flip extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<T, TKey>
     */
    public function __invoke(): Closure
    {
        $callbackForKeys =
            /**
             * @param mixed $carry
             * @param TKey $key
             * @param T $value
             *
             * @return T
             */
            static fn ($carry, $key, $value) => $value;

        $callbackForValues =
            /**
             * @param mixed $carry
             * @param TKey $key
             *
             * @return TKey
             */
            static fn ($carry, $key) => $key;

        /** @var Closure(Iterator<TKey, T>): Iterator<T, TKey> $associate */
        $associate = (new Associate())()($callbackForKeys)($callbackForValues);

        // Point free style.
        return $associate;
    }
}
