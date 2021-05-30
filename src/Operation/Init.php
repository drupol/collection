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

/**
 * @template TKey
 * @template T
 */
final class Init extends AbstractOperation
{
    /**
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
                $cacheIterator = new CachingIterator($iterator, CachingIterator::FULL_CACHE);

                foreach ($cacheIterator as $key => $current) {
                    if (false === $iterator->valid()) {
                        break;
                    }

                    yield $key => $current;
                }
            };
    }
}
