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
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Same extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Closure(callable(T, TKey): Closure(T, TKey): bool): Closure(Iterator<TKey, T>): Generator<int, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $other
             *
             * @return Closure(callable(T, TKey): Closure(T, TKey): bool): Closure(Iterator<TKey, T>): Generator<int, bool>
             */
            static fn (Iterator $other): Closure =>
                /**
                 * @param callable(T, TKey): (Closure(T, TKey): bool) $comparatorCallback
                 *
                 * @return Closure(Iterator<TKey, T>): Generator<int, bool>
                 */
                static fn (callable $comparatorCallback): Closure =>
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return Generator<int, bool>
                     */
                    static function (Iterator $iterator) use ($other, $comparatorCallback): Generator {
                        $isSame = true;

                        while ($iterator->valid() && $other->valid()) {
                            if (!$comparatorCallback($iterator->current(), $iterator->key())($other->current(), $other->key())) {
                                $isSame = false;
                            }

                            $iterator->next();
                            $other->next();
                        }

                        if ($iterator->valid() !== $other->valid()) {
                            $isSame = false;
                        }

                        yield $isSame;
                    };
    }
}
