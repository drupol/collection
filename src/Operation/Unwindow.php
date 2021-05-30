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
 * @template TKey
 * @template T
 */
final class Unwindow extends AbstractOperation
{
    /**
     * @return Closure(Iterator<TKey, list<T>>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        /** @var Closure(Iterator<TKey, list<T>>): Generator<TKey, T> $unwindow */
        $unwindow = Map::of()(
            /**
             * @param iterable<TKey, list<T>> $iterable
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
