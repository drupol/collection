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

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 */
final class Unwords extends AbstractOperation
{
    /**
     * @psalm-return Closure(Iterator<TKey, (T|string)>): Generator<TKey, string, mixed, void>
     */
    public function __invoke(): Closure
    {
        /** @psalm-var Closure(Iterator<TKey, (T | string)>): Generator<TKey, string> $implode */
        $implode = Implode::of()(' ');

        // Point free style.
        return $implode;
    }
}
