<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Iterator;
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
     * @return Closure(T):Closure(T):Closure(Iterator<TKey, T>):Iterator<int, T|iterable<int, T>>
     */
    public function __invoke(): Closure
    {
        return
            /**
             * @param T $key
             *
             * @return Closure(T): Closure(Iterator<TKey, T>): Iterator<int, T|iterable<int, T>>
             */
            static fn ($key): Closure =>
                /**
                 * @param T $default
                 *
                 * @return Closure(Iterator<TKey, T>): Iterator<int, T|iterable<int, T>>
                 */
                static fn ($default): Closure =>
                    /**
                     * @param Iterator<TKey, T> $iterator
                     *
                     * @return Iterator<int, iterable<int, T>|T>
                     */
                    static function (Iterator $iterator) use ($key, $default): Iterator {
                        $pick =
                            /**
                             * @param Iterator<TKey, T> $iterator
                             * @param iterable<TKey, T>|T $target
                             * @param array<int, string> $key
                             * @param T $default
                             *
                             * @return iterable<int, T>|T
                             */
                            static function (Iterator $iterator, $target, array $key, $default = null) use (&$pick) {
                                while (null !== $segment = array_shift($key)) {
                                    if ('*' === $segment) {
                                        if (false === is_iterable($target)) {
                                            return $default;
                                        }

                                        /** @var array<int, T> $result */
                                        $result = [];

                                        foreach ($target as $item) {
                                            $result[] = $pick($iterator, $item, $key);
                                        }

                                        /** @var Iterator<TKey, T> $collapse */
                                        $collapse = (new Collapse())()(new ArrayIterator($result));

                                        return in_array('*', $key, true) ? $collapse : $result;
                                    }

                                    if ((true === is_array($target)) && (true === array_key_exists($segment, $target))) {
                                        /** @var T $target */
                                        $target = $target[$segment];
                                    } elseif (($target instanceof ArrayAccess) && (true === $target->offsetExists($segment))) {
                                        /** @var T $target */
                                        $target = $target[$segment];
                                    } elseif ($target instanceof Collection) {
                                        /** @var T $target */
                                        $target = ((new Get())()($segment)($default)($target->getIterator()))->current();
                                    } elseif ((true === is_object($target)) && (true === property_exists($target, $segment))) {
                                        /** @var T $target */
                                        $target = (new ReflectionClass($target))->getProperty($segment)->getValue($target);
                                    } else {
                                        $target = $default;
                                    }
                                }

                                return $target;
                            };

                        $key = true === is_scalar($key) ? explode('.', trim((string) $key, '.')) : $key;

                        foreach ($iterator as $value) {
                            yield $pick($iterator, $value, $key, $default);
                        }
                    };
    }
}
