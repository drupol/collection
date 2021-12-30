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
final class Equals extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Closure(Iterator<TKey, T>): Generator<int|TKey, bool>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $other
             *
             * @return Closure(Iterator<TKey, T>): Generator<int|TKey, bool>
             */
            static function (Iterator $other): Closure {
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Generator<int|TKey, bool>
                 */
                return static function (Iterator $iterator) use ($other): Generator {
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

    /**
     * @pure
     */
    public static function of(): Closure
    {
        return (new self())->__invoke();
    }
}
