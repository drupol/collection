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
final class Current extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int $index): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static function (int $index): Closure {
                /** @var Closure(Iterator<TKey, T>): Iterator<TKey, T> $limit */
                $limit = (new Limit())()(1)($index);

                // Point free style.
                return $limit;
            };
    }
}
