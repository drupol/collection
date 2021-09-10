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
final class Inits extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int, list<T>>
     */
    public function __invoke(): Closure
    {
        $scanLeftCallback =
            /**
             * @param array<TKey, T> $carry
             * @param T $value
             * @param TKey $key
             *
             * @return array<TKey, T>
             */
            static function (array $carry, $value, $key): array {
                $carry[$key] = $value;

                return $carry;
            };

        // Point free style.
        return Pipe::ofTyped2(
            (new ScanLeft())()($scanLeftCallback)([]),
            (new Normalize())()
        );
    }
}
