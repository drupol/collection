<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use Exception;
use Generator;
use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * @implements Operation<TKey, T>
 */
final class Sort extends AbstractGeneratorOperation implements Operation
{
    public function __construct(int $type = Operation\Sortable::BY_VALUES, ?callable $callback = null)
    {
        $this->storage = [
            'type' => $type,
            'callback' => $callback ?? Closure::fromCallable([$this, 'compare']),
        ];
    }

    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param \Iterator<TKey, T> $iterator
             * @psalm-param callable(T, T):(int) $callback
             *
             * @psalm-return \Generator<TKey, T>
             */
            static function (Iterator $iterator, int $type, callable $callback): Generator {
                switch ($type) {
                    case Operation\Sortable::BY_VALUES:
                        $operations = [
                            'before' => [new Wrap()],
                            'after' => [new Unwrap()],
                        ];

                        break;
                    case Operation\Sortable::BY_KEYS:
                        $operations = [
                            'before' => [new Flip(), new Wrap()],
                            'after' => [new Unwrap(), new Flip()],
                        ];

                        break;

                    default:
                        throw new Exception('Invalid sort type.');
                }

                $callback =
                    /**
                     * @psalm-param array{TKey, T} $left
                     * @psalm-param array{TKey, T} $right
                     */
                    static function (array $left, array $right) use ($callback): int {
                        /** @psalm-var T $left */
                        $left = current($left);
                        /** @psalm-var T $right */
                        $right = current($right);

                        return $callback($left, $right);
                    };

                /** @psalm-var \Iterator<int, array{TKey, T}> $newIterator */
                $newIterator = (new Run())()($iterator, ...$operations['before']);
                $arrayIterator = new ArrayIterator(iterator_to_array($newIterator));
                $arrayIterator->uasort($callback);

                return yield from (new Run())()($arrayIterator, ...$operations['after']);
            };
    }

    /**
     * @psalm-param T $left
     * @psalm-param T $right
     *
     * @param mixed $left
     * @param mixed $right
     */
    private function compare($left, $right): int
    {
        return $left <=> $right;
    }
}
