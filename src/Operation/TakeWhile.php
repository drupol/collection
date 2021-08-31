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
use loophp\collection\Utils\CallbacksArrayReducer;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class TakeWhile implements Operation
{
    /**
     * @pure
     *
     * @param callable(T=, TKey=, Iterator<TKey, T>=): bool ...$callbacks
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(callable ...$callbacks): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<TKey, T>
             */
            static function (Iterator $iterator) use ($callbacks): Iterator {
                foreach ($iterator as $key => $current) {
                    if (!CallbacksArrayReducer::or()($callbacks, $current, $key, $iterator)) {
                        break;
                    }

                    yield $key => $current;
                }
            };
    }
}
