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
final class Sort implements Operation
{
    /**
     * @pure
     *
     * @return Closure(callable(T|TKey, T|TKey): int): Closure(Iterator<TKey, T>): Iterator<TKey, T>
     */
    public function __invoke(int $type = Operation\Sortable::BY_VALUES): Closure
    {
        return
            /**
             * @return Closure(Iterator<TKey, T>): Iterator<TKey, T>
             */
            static function (?callable $callback = null) use ($type): Closure {
                $callback ??=
                /**
                 * @param T|TKey $left
                 * @param T|TKey $right
                 */
                static fn ($left, $right): int => $left <=> $right;

                return
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<TKey, T>
                 */
                static function (Iterator $iterator) use ($type, $callback): Iterator {
                    if (Operation\Sortable::BY_VALUES !== $type && Operation\Sortable::BY_KEYS !== $type) {
                        throw new Exception('Invalid sort type.');
                    }

                    $operations = Operation\Sortable::BY_VALUES === $type ?
                        [
                            'before' => [(new Pack())()],
                            'after' => [(new Unpack())()],
                        ] :
                        [
                            'before' => [(new Flip())(), (new Pack())()],
                            'after' => [(new Unpack())(), (new Flip())()],
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

                    $before = Pipe::ofVariadic(...$operations['before']);

                    $arrayIterator = new ArrayIterator([...$before($iterator)]);
                    $arrayIterator->uasort($sortCallback($callback));

                    return Pipe::ofVariadic(...$operations['after'])($arrayIterator);
                };
            };
    }
}
