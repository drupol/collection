<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Closure;
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
final class Partition implements Operation
{
    /**
     * @pure
     *
     * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): Closure(Iterator<TKey, T>): Iterator<int, array{0: Closure(Iterator<TKey, T>): Iterator<TKey, T>, 1: array{0: Iterator<TKey, T>}}>
     */
    public function __invoke(): Closure
    {
        return
            (
                /**
                 * @param array{0:(Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): (Closure(Iterator<TKey, T>): Iterator<TKey, T>)), 1:(Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): (Closure(Iterator<TKey, T>): Iterator<TKey, T>))} $operations
                 *
                 * @return Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): Closure(Iterator<TKey, T>): Iterator<int, array{0: Closure(Iterator<TKey, T>): Iterator<TKey, T>, 1: array{0: Iterator<TKey, T>}}>
                 */
                static fn (array $operations): Closure =>
                /**
                 * @param callable(T=, TKey=, Iterator<TKey, T>=): bool ...$callbacks
                 *
                 * @return Closure(Iterator<TKey, T>): Iterator<int, array{0: Closure(Iterator<TKey, T>): Iterator<TKey, T>, 1: array{0: Iterator<TKey, T>}}>
                 */
                static fn (callable ...$callbacks): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<int, array{0: Closure(Iterator<TKey, T>): Iterator<TKey, T>, 1: array{0: Iterator<TKey, T>}}>
                 */
                static fn (Iterator $iterator): Iterator => yield from array_map(
                    /**
                     * @param Closure(callable(T=, TKey=, Iterator<TKey, T>=): bool ...): (Closure(Iterator<TKey, T>): Iterator<TKey, T>) $callable
                     *
                     * @return array{0: (Closure(Iterator<TKey, T>): Iterator<TKey, T>), 1: (array{0: Iterator<TKey, T>})}
                     */
                    static fn (callable $callable): array => [$callable(...$callbacks), [$iterator]],
                    $operations
                )
            )([(new Filter()), (new Reject())]);
    }
}
