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
 */
final class Append extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T...): Closure(Iterator<TKey, T>): Iterator<int|TKey, T>
     */
    public function __invoke(): Closure
    {
        return IteratorFactory::appendIterator();
    }
}
