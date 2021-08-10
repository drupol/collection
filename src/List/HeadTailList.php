<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation\List;

use loophp\collection\Contract\List\HeadTailList as HeadTailListInterface;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 */
final class HeadTailList implements HeadTailListInterface
{
    private $head;

    private HeadTailListInterface $tail;

    public function __construct($head, HeadTailListInterface $tail)
    {
        $this->head = $head;
        $this->tail = $tail;
    }

    public static function fromArray(array $array): HeadTailListInterface
    {
        return ($x = array_shift($array)) ? new HeadTailList($x, HeadTailList::fromArray($array)) : new NilHeadTailList();
    }

    public function head()
    {
        return $this->head;
    }

    public function tail(): HeadTailListInterface
    {
        return $this->tail;
    }
}
