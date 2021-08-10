<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation\List;

use loophp\collection\Contract\List\HeadTailList as HeadTailListInterface;
use RuntimeException;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class NilHeadTailList implements HeadTailListInterface
{
    public function head()
    {
        throw new RuntimeException('No head present');
    }

    public function tail(): HeadTailListInterface
    {
        throw new RuntimeException('No tail present');
    }
}
