<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Iterator;

use Iterator;

/**
 * @internal
 *
 * @psalm-template TKey
 * @psalm-template TKey
 * @psalm-template T of string
 *
 * @extends ProxyIterator<TKey, T>
 */
final class HeadTailIterator2
{
    private mixed $head;

    private Iterator $tail;

    private function __construct($head, Iterator $tail)
    {
        $this->head = $head;
        $this->tail = $tail;
    }

    public function current(): array
    {
        return [$this->head, $this->tail];
    }

    /**
     * @return string
     *
     * @psalm-return T
     */
    public function head()
    {
        return current($this::current());
    }

    public function next(): void
    {
        parent::next();

        $this->tail = new HeadTailIterator($this);
    }

    public function tail(): HeadTailIterator
    {
        return $this->tail;
    }
}
