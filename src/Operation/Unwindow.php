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
final class Unwindow extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, list<T>>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        /** @var Closure(Iterator<TKey, list<T>>): Generator<TKey, T> $unwindow */
        $unwindow = Map::of()(FPT::end()(null));

        // Point free style.
        return $unwindow;
    }
}
