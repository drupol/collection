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
use loophp\collection\Utils\CallbacksArrayReducer;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Reject implements Operation
{
    /**
     * @pure
     *
     * @param callable(T=, TKey=, Iterator<TKey, T>=): bool ...$callbacks
     *
     * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(callable ...$callbacks): Closure
    {
        $defaultCallback =
            /**
             * @param T $value
             */
            static fn ($value): bool => (bool) $value;

        $callbacks = [] === $callbacks ?
            [$defaultCallback] :
            $callbacks;

        // Point free style.
        return (new Filter())(
            /**
             * @param T $current
             * @param TKey $key
             * @param Iterator<TKey, T> $iterator
             */
            static fn ($current, $key, Iterator $iterator): bool => !CallbacksArrayReducer::or()($callbacks, $current, $key, $iterator)
        );
    }
}
