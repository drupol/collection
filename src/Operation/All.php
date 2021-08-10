<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Iterator;
use loophp\fpt\Curry;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class All extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(Iterator<TKey, T>): array<TKey, T>
     */
    public function __invoke(): Closure
    {
        return Curry::of()('iterator_to_array');
    }
}
