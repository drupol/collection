<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;

use function in_array;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Compact extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T...): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T ...$values
             *
             * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static function (...$values): Closure {
                $filterCallback =
                    /**
                     * @param non-empty-array<int, (null|array|int|bool|string|T)> $values
                     */
                    static fn (array $values): Closure =>
                        /**
                         * @param T $value
                         */
                        static fn ($value): bool => !in_array($value, $values, true);

                $filter = (new Filter())()(
                    $filterCallback(
                        [] === $values ?
                            Nullsy::VALUES :
                            $values
                    )
                );

                // Point free style.
                return $filter;
            };
    }
}
