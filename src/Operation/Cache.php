<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\collection\Iterator\PsrCacheIterator;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Cache extends AbstractOperation
{
    /**
     * @psalm-return Closure(CacheItemPoolInterface): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-return Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static fn (CacheItemPoolInterface $cache): Closure =>
                /**
                 * @psalm-param Iterator<TKey, T> $iterator
                 *
                 * @psalm-return Iterator<TKey, T>
                 */
                static fn (Iterator $iterator): Iterator => new PsrCacheIterator($iterator, $cache);
    }
}
