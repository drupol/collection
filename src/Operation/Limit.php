<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Iterator\IteratorFactory;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Limit extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int): Closure(int): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(int): Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (int $count = -1): Closure =>
                /**
                 * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
                 */
                static fn (int $offset = 0): Closure => IteratorFactory::limitIterator()($offset)($count);
    }
}
