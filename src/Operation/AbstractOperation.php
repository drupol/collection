<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use loophp\collection\Contract\Operation;

abstract class AbstractOperation implements Operation
{
    final public function __construct()
    {
    }

    public static function of(): Closure
    {
        return (new static())->__invoke();
    }
}
