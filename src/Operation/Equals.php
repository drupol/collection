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
final class Equals extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Closure(Iterator<TKey, T>): Iterator<int, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $other
             *
             * @return Closure(Iterator<TKey, T>): Iterator<int, bool>
             */
            static function (Iterator $other): Closure {
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<int, bool>
                 */
                return static function (Iterator $iterator) use ($other): Iterator {
                    while ($other->valid() && $iterator->valid()) {
                        $iterator->next();
                        $other->next();
                    }

                    if ($other->valid() !== $iterator->valid()) {
                        return yield false;
                    }

                    $containsCallback =
                        /**
                         * @param T $current
                         */
                        static fn ($current): bool => (new Contains())()($current)($other)->current();

                    return yield from (new Every())()(static fn (): bool => false)($containsCallback)($iterator);
                };
            };
    }
}
