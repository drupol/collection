<?php

declare(strict_types=1);

namespace loophp\collection\Operation;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Generator;
use Iterator;
use loophp\collection\Contract\Collection;
use loophp\collection\Contract\Operation;
use loophp\collection\Iterator\IterableIterator;
use ReflectionClass;
use ReflectionException;

use function array_key_exists;
use function in_array;
use function is_array;
use function is_object;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * @implements Operation<TKey, T>
 */
final class Pluck extends AbstractOperation implements Operation
{
    /**
     * Pluck constructor.
     *
     * @param array<int, string>|string $key
     * @param mixed $default
     */
    public function __construct($key, $default)
    {
        $this->storage = [
            'key' => $key,
            'default' => $default,
            'operation' => Closure::fromCallable([$this, 'pick']),
        ];
    }

    public function __invoke(): Closure
    {
        return
            /**
             * @psalm-param \Iterator<TKey, T> $iterator
             *
             * @param array<int, string>|string $key
             * @param mixed $default
             * @psalm-param callable(\Iterator<TKey, T>, T, array<int, string>, T):(array<int, T>|T) $pick
             *
             * @psalm-return \Generator<int, T|array<int, T>>
             */
            static function (Iterator $iterator, $key, $default, callable $pick): Generator {
                $key = true === is_scalar($key) ? explode('.', trim((string) $key, '.')) : $key;

                foreach ($iterator as $value) {
                    yield $pick($iterator, $value, $key, $default);
                }
            };
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param Iterator<mixed> $iterator
     * @psalm-param Iterator<TKey, T> $iterator
     *
     * @param mixed $target
     * @psalm-param T $target
     *
     * @param array<string> $key
     * @psalm-param array<int, string> $key
     *
     * @param mixed $default
     * @psalm-param T $default
     *
     * @throws ReflectionException
     *
     * @return mixed
     * @psalm-return T|array<int, T>
     */
    private function pick(Iterator $iterator, $target, array $key, $default = null)
    {
        while (null !== $segment = array_shift($key)) {
            if ('*' === $segment) {
                if (false === is_iterable($target)) {
                    return $default;
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = $this->pick($iterator, $item, $key);
                }

                return in_array('*', $key, true) ? (new Run())()(new ArrayIterator($result), new Collapse()) : $result;
            }

            if ((true === is_array($target)) && (true === array_key_exists($segment, $target))) {
                $target = $target[$segment];
            } elseif (($target instanceof ArrayAccess) && (true === $target->offsetExists($segment))) {
                $target = $target[$segment];
            } elseif ($target instanceof Collection) {
                $target = (new Run())()(new IterableIterator($target), new Get($segment, $default));
            } elseif ((true === is_object($target)) && (true === property_exists($target, $segment))) {
                $target = (new ReflectionClass($target))->getProperty($segment)->getValue($target);
            } else {
                $target = $default;
            }
        }

        return $target;
    }
}
