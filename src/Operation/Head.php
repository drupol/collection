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
final class Head extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<TKey, T>
             */
            static function (Iterator $iterator): Iterator {
                $isEmpty = true;

                foreach ($iterator as $key => $current) {
                    $isEmpty = false;

                    break;
                }

                if (false === $isEmpty) {
                    /**
                     * @var TKey $key
                     * @var T $current
                     */
                    return yield $key => $current;
                }
            };
    }
}
