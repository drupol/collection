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
final class HeadTailIterator extends ProxyIterator
{
    private $tail;

    /**
     * @psalm-param Iterator<TKey, T> $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function current(): array
    {
        return [parent::current(), $this->tail];
    }

    /**
     * @return string
     *
     * @psalm-return T
     */
    public function head()
    {
        return parent::current();
    }

    public function next(): void
    {
        parent::next();

        $this->tail = new HeadTailIterator($this);
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->next();
    }

    public function tail(): HeadTailIterator
    {
        return $this->tail;
    }
}
