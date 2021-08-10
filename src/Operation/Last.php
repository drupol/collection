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
final class Last extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Generator<TKey, T>
             */
            static function (Iterator $iterator): Generator {
                $isEmpty = true;

                foreach ($iterator as $key => $current) {
                    $isEmpty = false;
                }

                if (true === $isEmpty) {
                    return yield from [];
                }

                /**
                 * @var TKey $key
                 * @var T $current
                 */
                return yield $key => $current;
            };
    }
}
