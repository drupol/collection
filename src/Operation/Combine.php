<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use Iterator;
use loophp\collection\Contract\Operation;
use MultipleIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Combine implements Operation
{
    /**
     * @pure
     *
     * @template U
     *
     * @param U ...$keys
     *
     * @return Closure(Iterator<TKey, T>): Iterator<null|U, null|T>
     */
    public function __invoke(...$keys): Closure
    {
        $buildMultipleIterator =
            /**
             * @param Iterator<int, U> $keyIterator
             */
            static function (Iterator $keyIterator): Closure {
                return
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return MultipleIterator
                     */
                    static function (Iterator $iterator) use ($keyIterator): MultipleIterator {
                        $mit = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);

                        $mit->attachIterator($keyIterator);
                        $mit->attachIterator($iterator);

                        return $mit;
                    };
            };

        // Point free style.
        return Pipe::ofTyped3(
            $buildMultipleIterator(new ArrayIterator($keys)),
            (new Flatten())(1),
            (new Pair())(),
        );
    }
}
