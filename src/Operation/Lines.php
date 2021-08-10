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

use const PHP_EOL;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Lines extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Generator<int, string>
     */
    public function __invoke(): Closure
    {
        /** @var Closure(Iterator<TKey, (T|string)>): Generator<int, string> $pipe */
        $pipe = Pipe::of()(
            Explode::of()(PHP_EOL, "\n", "\r\n"),
            Map::of()(
                FPT::curry()('implode', 2)('')
            )
        );

        // Point free style.
        return $pipe;
    }
}
