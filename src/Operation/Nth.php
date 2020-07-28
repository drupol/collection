<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
use Generator;
use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @template TKey
 * @psalm-template TKey of array-key
 * @template T
 * @extends AbstractOperation<TKey, T, Generator<TKey, T>>
 * @implements Operation<TKey, T, Generator<TKey, T>>
 */
final class Nth extends AbstractOperation implements Operation
{
    public function __construct(int $step, int $offset)
    {
        $this->storage = [
            'step' => $step,
            'offset' => $offset,
        ];
    }

    /**
     * @return Closure(\Iterator<TKey, T>, int, int): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-return \Generator<TKey, T>
             */
            static function (Iterator $iterator, int $step, int $offset): Generator {
                $position = 0;

                foreach ($iterator as $key => $value) {
                    if ($position++ % $step !== $offset) {
                        continue;
                    }

                    yield $key => $value;
                }
            };
    }
}
