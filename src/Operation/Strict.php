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
use loophp\collection\Iterator\TypedIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Strict implements Operation
{
    /**
     * @pure
     *
     * @param null|callable(mixed): string $callback
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(?callable $callback = null): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<TKey, T>
             */
            static fn (Iterator $iterator): Iterator => new TypedIterator($iterator, $callback);
    }
}
