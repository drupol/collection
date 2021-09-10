<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class Coalesce extends AbstractOperation
{
    /**
     * @pure
     */
    public function __invoke(): Closure
    {
        // Point free style.
        return Pipe::ofTyped2(
            (new Compact())()(),
            (new Head())(),
        );
    }
}
