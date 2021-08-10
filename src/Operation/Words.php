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
final class Words extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Generator<TKey, string>
     */
    public function __invoke(): Closure
    {
        /** @var Closure(Iterator<TKey, T>): Generator<TKey, string> $pipe */
        $pipe = Pipe::of()(
            Explode::of()("\t", "\n", ' '),
            Map::of()(
                // TODO: See if we can get rid of the parameter 2.
                FPT::curry()('implode', 2)('')
            ),
            Compact::of()()
        );

        // Point free style.
        return $pipe;
    }
}
