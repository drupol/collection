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
use loophp\collection\Iterator\IterableIterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Flatten implements Operation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<mixed, mixed>
     */
    public function __invoke(int $depth): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             */
            static function (Iterator $iterator) use ($depth): Iterator {
                foreach ($iterator as $key => $value) {
                    if (false === is_iterable($value)) {
                        yield $key => $value;

                        continue;
                    }

                    if (1 !== $depth) {
                        $flatten = (new Flatten())($depth - 1);

                        $value = $flatten(new IterableIterator($value));
                    }

                    yield from $value;
                }
            };
    }
}
