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
final class RSample extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(float): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static function (float $probability): Closure {
                $callback = static fn (): bool => (mt_rand() / mt_getrandmax()) < $probability;

                $filter = (new Filter())()($callback);

                // Point free style.
                return $filter;
            };
    }
}
