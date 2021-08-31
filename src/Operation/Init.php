<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use CachingIterator;
use Closure;
use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Init implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        $callback =
            /**
             * @param T $value
             * @param TKey $key
             * @param CachingIterator<TKey, T> $iterator
             */
            static fn ($value, $key, CachingIterator $iterator): bool => $iterator->hasNext();

        $buildCachingIterator =
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return CachingIterator<TKey, T>
             */
            static fn (Iterator $iterator): CachingIterator => new CachingIterator($iterator, CachingIterator::FULL_CACHE);

        return Pipe::ofTyped2(
            $buildCachingIterator,
            (new TakeWhile())($callback)
        );
    }
}
