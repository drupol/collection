<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Collapse extends AbstractOperation
{
    /**
     * @pure
     *
     * @psalm-suppress ImpureFunctionCall - using Filter and Flatten as an internal tools, not returned.
     *
     * @return Closure(Iterator<TKey, (T|iterable<TKey, T>)>): Iterator<TKey, T>
     */
    public function __invoke(): Closure
    {
        $filterCallback =
            /**
             * @param T $value
             */
            static fn ($value): bool => is_iterable($value);

        /** @var Closure(Iterator<TKey, (T|iterable<TKey, T>)>): Iterator<TKey, T> $pipe */
        $pipe = Pipe::ofTyped2(
            (new Filter())()($filterCallback),
            (new Flatten())()(1),
        );

        // Point free style.
        return $pipe;
    }
}
