<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use Amp\Sync\LocalSemaphore;
use Closure;
use Exception;
use Generator;
use Iterator;

use function Amp\Iterator\fromIterable;
use function Amp\ParallelFunctions\parallel;
use function Amp\Promise\wait;
use function Amp\Sync\ConcurrentIterator\map;
use function function_exists;

// phpcs:disable
if (false === function_exists('Amp\ParallelFunctions\parallel')) {
    throw new Exception('You need amphp/parallel-functions to get this operation working.');
}
// phpcs:enable
/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class AsyncMap extends AbstractOperation
{
    /**
     * @pure
     *
     * @template V
     *
     * @return Closure(callable(T, TKey): V): Closure(Iterator<TKey, T>): Generator<TKey, V>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(T, TKey): V $callback
             */
            static fn (callable $callback): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Generator<TKey, V>
                 */
                static function (Iterator $iterator) use ($callback): Generator {
                    $parallelCallBack =
                        /**
                         * @param array{0: TKey, 1: T} $value
                         *
                         * @return array{0: TKey, 1: V}
                         */
                        static fn (array $value): array => [$value[0], $callback($value[1], $value[0])];

                    $iter = map(fromIterable(Pack::of()($iterator)), new LocalSemaphore(32), parallel($parallelCallBack));

                    while (wait($iter->advance())) {
                        /** @var array{0: TKey, 1: V} $item */
                        $item = $iter->getCurrent();

                        yield $item[0] => $item[1];
                    }
                };
    }
}
