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
final class Unwrap extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): Iterator<mixed, mixed>
     */
    public function __invoke(): Closure
    {
        /** @var Closure(Iterator<TKey, T>): Iterator<mixed, mixed> $flatten */
        $flatten = (new Flatten())()(1);

        // Point free style.
        return $flatten;
    }
}
