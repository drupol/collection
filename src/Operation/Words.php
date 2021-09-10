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
final class Words extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, string>
     */
    public function __invoke(): Closure
    {
        $mapCallback =
            /**
             * @param list<string> $value
             */
            static fn (array $value): string => implode('', $value);

        // Point free style.
        return Pipe::ofTyped3(
            (new Explode())()("\t", "\n", ' '),
            (new Map())()($mapCallback),
            (new Compact())()()
        );
    }
}
