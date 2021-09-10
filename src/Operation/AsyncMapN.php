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
final class AsyncMapN extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(callable(mixed, mixed): mixed ...): Closure(Iterator<TKey, T>): Iterator<mixed, mixed>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param callable(mixed, mixed): mixed ...$callbacks
             */
            static fn (callable ...$callbacks): Closure =>
                /**
                 * @param Iterator<TKey, T> $iterator
                 *
                 * @return Iterator<mixed, mixed>
                 */
                static function (Iterator $iterator) use ($callbacks): Iterator {
                    $callbackFactory =
                        /**
                         * @param mixed $key
                         *
                         * @return Closure(mixed, callable(mixed, mixed): mixed): mixed
                         */
                        static fn ($key): Closure =>
                            /**
                             * @param mixed $carry
                             * @param callable(mixed, mixed): mixed $callback
                             *
                             * @return mixed
                             */
                            static fn ($carry, callable $callback) => $callback($carry, $key);

                    $callback =
                        /**
                         * @param array{0: mixed, 1: mixed} $value
                         *
                         * @return array{0: mixed, 1: mixed}
                         */
                        static fn (array $value): array => [$value[0], array_reduce($callbacks, $callbackFactory($value[0]), $value[1])];

                    $iter = map(fromIterable((new Pack())()($iterator)), new LocalSemaphore(32), parallel($callback));

                    while (wait($iter->advance())) {
                        /** @var array{0: mixed, 1: mixed} $item */
                        $item = $iter->getCurrent();

                        yield $item[0] => $item[1];
                    }
                };
    }
}
