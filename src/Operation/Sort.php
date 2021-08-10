<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayIterator;
use Closure;
use Exception;
use Generator;
use Iterator;
use loophp\collection\Contract\Operation;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Sort extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(int): Closure(callable(T|TKey, T|TKey): int): Closure(Iterator<TKey, T>): Generator<TKey, T>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @return Closure(callable(T|TKey, T|TKey): int): Closure(Iterator<TKey, T>): Generator<TKey, T>
             */
            static fn (int $type = Operation\Sortable::BY_VALUES): Closure =>
                /**
                 * @return Closure(Iterator<TKey, T>): Generator<TKey, T>
                 */
                static fn (?callable $callback = null): Closure =>

                        /**
                         * @param Iterator<TKey, T> $iterator
                         *
                         * @return Generator<TKey, T>
                         */
                        static function (Iterator $iterator) use ($type, $callback): Iterator {
                            if (Operation\Sortable::BY_VALUES !== $type && Operation\Sortable::BY_KEYS !== $type) {
                                throw new Exception('Invalid sort type.');
                            }

                            $operations = Operation\Sortable::BY_VALUES === $type ?
                                [
                                    'before' => [Pack::of()],
                                    'after' => [Unpack::of()],
                                ] :
                                [
                                    'before' => [Flip::of(), Pack::of()],
                                    'after' => [Unpack::of(), Flip::of()],
                                ];

                            $sortCallback =
                                /**
                                 * @param callable(T|TKey, T|TKey): int $callback
                                 *
                                 * @return Closure(array{0:TKey|T, 1:T|TKey}, array{0:TKey|T, 1:T|TKey}): int
                                 */
                                static fn (callable $callback): Closure =>
                                    /**
                                     * @param array{0:TKey|T, 1:T|TKey} $left
                                     * @param array{0:TKey|T, 1:T|TKey} $right
                                     */
                                    static fn (array $left, array $right): int => $callback($left[1], $right[1]);

                            /** @var callable(Iterator<TKey, T>): Generator<int, array{0:TKey, 1:T}> | callable(Iterator<TKey, T>): Generator<int, array{0:T, 1:TKey}> $before */
                            $before = Pipe::of()(...$operations['before']);

                            $arrayIterator = new ArrayIterator([...$before($iterator)]);
                            $arrayIterator->uasort(
                                $sortCallback(
                                    $callback ??
                                        /**
                                         * @param T|TKey $left
                                         * @param T|TKey $right
                                         */
                                        static fn ($left, $right): int => $left <=> $right
                                )
                            );

                            return Pipe::of()(...$operations['after'])($arrayIterator);
                        };
    }
}
