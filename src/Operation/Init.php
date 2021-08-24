<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use CachingIterator;
use Closure;
use Generator;
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
final class Init extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        $callback =
            /**
             * @param T $value
             * @param TKey $key
             * @param CachingIterator<TKey, T> $iterator
             */
            static fn ($value, $key, CachingIterator $iterator): bool => $iterator->getInnerIterator()->valid();

        /** @var Closure(Iterator<TKey, T>): Generator<TKey, T> $takeWhile */
        $takeWhile = Pipe::of()(
            IteratorFactory::cachingIterator()(CachingIterator::FULL_CACHE),
            (new TakeWhile())()($callback)
        );

        // Point free style.
        return $takeWhile;
    }
}
