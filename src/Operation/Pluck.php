<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayAccess;
use Closure;
use Generator;
use loophp\collection\Contract\Collection;
use ReflectionClass;

use function array_key_exists;
use function in_array;
use function is_array;
use function is_object;

/**
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class Pluck extends AbstractOperation
{
    /**
     * @pure
     *
     * @return Closure(T):Closure(T):Closure(iterable<TKey, T>):Generator<int, T|iterable<int, T>, mixed, void>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T $key
             *
             * @return Closure(T): Closure(iterable<TKey, T>): Generator<int, T|iterable<int, T>, mixed, void>
             */
            static fn ($key): Closure =>
                /**
                 * @param T $default
                 *
                 * @return Closure(iterable<TKey, T>): Generator<int, T|iterable<int, T>, mixed, void>
                 */
                static fn ($default): Closure =>
                    /**
                     * @param iterable<TKey, T> $iterable
                     *
                     * @return Generator<int, T|iterable<int, T>, mixed, void>
                     */
                    static function (iterable $iterable) use ($key, $default): Generator {
                        $pick =
                            /**
                             * @param iterable<TKey, T> $iterable
                             * @param iterable<TKey, T>|T $target
                             * @param array<int, string> $key
                             * @param T $default
                             *
                             * @return iterable<int, T>|T
                             */
                            static function (iterable $iterable, $target, array $key, $default = null) use (&$pick) {
                                while (null !== $segment = array_shift($key)) {
                                    if ('*' === $segment) {
                                        if (!is_iterable($target)) {
                                            return $default;
                                        }

                                        /** @var array<int, T> $result */
                                        $result = [];

                                        foreach ($target as $item) {
                                            $result[] = $pick($iterable, $item, $key);
                                        }

                                        /** @var Generator<TKey, T> $collapse */
                                        $collapse = Collapse::of()($result);

                                        return in_array('*', $key, true) ? $collapse : $result;
                                    }

                                    if (is_array($target) && array_key_exists($segment, $target)) {
                                        /** @var T $target */
                                        $target = $target[$segment];
                                    } elseif (($target instanceof ArrayAccess) && ($target->offsetExists($segment))) {
                                        /** @var T $target */
                                        $target = $target[$segment];
                                    } elseif ($target instanceof Collection) {
                                        /** @var T $target */
                                        $target = (Get::of()($segment)($default)($target))->current();
                                    } elseif (is_object($target) && property_exists($target, $segment)) {
                                        /** @var T $target */
                                        $target = (new ReflectionClass($target))->getProperty($segment)->getValue($target);
                                    } else {
                                        $target = $default;
                                    }
                                }

                                return $target;
                            };

                        $key = is_scalar($key) ? explode('.', trim((string) $key, '.')) : $key;

                        foreach ($iterable as $value) {
                            yield $pick($iterable, $value, $key, $default);
                        }
                    };
    }
}
