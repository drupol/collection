<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use Iterator;
use loophp\collection\Contract\EagerOperation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * @implements EagerOperation<TKey, T>
 */
final class Contains extends AbstractEagerOperation implements EagerOperation
{
    /**
     * @param mixed ...$values
     * @psalm-param T ...$values
     */
    public function __construct(...$values)
    {
        $this->storage['values'] = new ArrayIterator($values);
    }

    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param \Iterator<TKey, T> $collection
             * @psalm-param \ArrayIterator<int, T> $values
             */
            static function (Iterator $collection, ArrayIterator $values): bool {
                foreach ($collection as $key => $value) {
                    foreach ($values as $k => $v) {
                        if ($v === $value) {
                            unset($values[$k]);
                        }

                        if (0 === $values->count()) {
                            return true;
                        }
                    }
                }

                return false;
            };
    }
}
