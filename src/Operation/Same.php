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
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Same extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Closure(callable(T, TKey): Closure(T, TKey): bool): Closure(Iterator<TKey, T>): Iterator<int, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $other
             *
             * @return Closure(callable(T, TKey): Closure(T, TKey): bool): Closure(Iterator<TKey, T>): Iterator<int, bool>
             */
            static fn (Iterator $other): Closure =>
                /**
                 * @param callable(T, TKey): (Closure(T, TKey): bool) $comparatorCallback
                 *
                 * @return Closure(Iterator<TKey, T>): Iterator<int, bool>
                 */
                static fn (callable $comparatorCallback): Closure =>
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return Iterator<int, bool>
                     */
                    static function (Iterator $iterator) use ($other, $comparatorCallback): Iterator {
                        while ($iterator->valid() && $other->valid()) {
                            if (!$comparatorCallback($iterator->current(), $iterator->key())($other->current(), $other->key())) {
                                return yield false;
                            }

                            $iterator->next();
                            $other->next();
                        }

                        // TODO: Use single return line when FriendsOfPHP/PHP-CS-Fixer/issues/5810 is fixed.
                        if ($iterator->valid() !== $other->valid()) {
                            return yield false;
                        }

                        return yield true;
                    };
    }
}
