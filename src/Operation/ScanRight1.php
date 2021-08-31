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

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class ScanRight1 implements Operation
{
    /**
     * @pure
     *
     * @template V
     *
     * @param callable(T|V, T, TKey, Iterator<TKey, T>): V $callback
     *
     * @return Closure(Iterator<TKey, T>): Iterator<int|TKey, T|V>
     */
    public function __invoke(callable $callback): Closure
    {
        // Point free style.
        return Pipe::ofTyped3(
            (new Reverse())(),
            (new ScanLeft1())($callback),
            (new Reverse())()
        );
    }
}
