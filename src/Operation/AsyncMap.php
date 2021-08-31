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
use Iterator;
use loophp\collection\Contract\Operation;

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
final class AsyncMap implements Operation
{
    /**
     * @pure
     *
     * @template V
     *
     * @param callable(T, TKey): V $callback
     *
     * @return Closure(Iterator<TKey, T>): Iterator<TKey, V>
     */
    public function __invoke(callable $callback): Closure
    {
        return
            /**
             * @param Iterator<TKey, T> $iterator
             *
             * @return Iterator<TKey, V>
             */
            static function (Iterator $iterator) use ($callback): Iterator {
                $parallelCallBack =
                    /**
                     * @param array{0: TKey, 1: T} $value
                     *
                     * @return array{0: TKey, 1: V}
                     */
                    static fn (array $value): array => [$value[0], $callback($value[1], $value[0])];

                $iter = map(fromIterable((new Pack())()($iterator)), new LocalSemaphore(32), parallel($parallelCallBack));

                while (wait($iter->advance())) {
                    /** @var array{0: TKey, 1: V} $item */
                    $item = $iter->getCurrent();

                    yield $item[0] => $item[1];
                }
            };
    }
}
