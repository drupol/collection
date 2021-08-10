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
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Reduction extends AbstractOperation
{
    /**
     * @pure
     *
     * @template V
     * @template W
     *
     * @return Closure(callable(V|W, T, TKey, Iterator<TKey, T>): W): Closure(V): Closure(Iterator<TKey, T>): Generator<TKey, W>
     */
    public function __invoke(): Closure
    {
        return FPT::reduction();
    }
}
