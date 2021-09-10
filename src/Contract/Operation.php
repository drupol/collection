<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Contract;

use Closure;

/** @immutable */
interface Operation
{
    /**
     * @pure
     */
    public function __invoke(): Closure;
}
