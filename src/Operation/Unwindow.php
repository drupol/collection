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

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Unwindow implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, list<T>>): Iterator<TKey, T|null>
     */
    public function __invoke(): Closure
    {
        $unwindow = (new Map())(
            /**
             * @param iterable<TKey, T> $iterable
             *
             * @return T|null
             */
            static function (iterable $iterable) {
                $value = null;

                /** @var T $value */
                foreach ($iterable as $value) {
                }

                return $value;
            }
        );

        // Point free style.
        return $unwindow;
    }
}
