<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;
use loophp\fpt\FPT;

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
     * @return Closure(Iterator<TKey, (T|iterable<TKey, T>)>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        $pipe = Pipe::of()(
            Filter::of()(FPT::curry()('is_iterable')),
            Flatten::of()(1),
        );

        return $pipe;
    }
}
