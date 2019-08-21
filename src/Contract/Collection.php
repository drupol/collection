<?php

declare(strict_types=1);

namespace drupol\collection\Contract;

use drupol\collection\Contract\Collection as CollectionInterface;
use drupol\collection\Contract\Operation\Appendable;
use drupol\collection\Contract\Operation\Applyable;
use drupol\collection\Contract\Operation\Prependable;

/**
 * Interface Collection.
 */
interface Collection extends \Countable, \IteratorAggregate, Appendable, Applyable, Prependable
{
    /**
     * Get all items from the collection.
     *
     * @return array
     *   An array containing all the elements of the collection.
     */
    public function all(): array;

    /**
     * Chunk the collection into chunks of the given size.
     *
     * @param int $size
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function chunk(int $size): self;

    /**
     * Collapse the collection of items into a single array.
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function collapse(): self;

    /**
     * @param mixed $keys
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function combine($keys): self;

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function contains($key): bool;

    /**
     * {@inheritdoc}
     */
    public function count(): int;

    /**
     * Run a filter over each of the items.
     *
     * @param callable ...$callbacks
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function filter(callable ...$callbacks): self;

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @param null|callable $callback
     * @param mixed $default
     *
     * @return mixed
     */
    public function first(callable $callback = null, $default = null);

    /**
     * Get a flattened list of the items in the collection.
     *
     * @param int $depth
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function flatten(int $depth = \PHP_INT_MAX): self;

    /**
     * Flip the items in the collection.
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function flip(): self;

    /**
     * Remove an item by key.
     *
     * @param string ...$keys
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function forget(...$keys): self;

    /**
     * Get an item by key.
     *
     * @param int|string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Get the values iterator.
     *
     * // This could be removed but then PHPStan will complain.
     *
     * @return \Iterator
     */
    public function getIterator();

    /**
     * Insert a given value between each element of a collection.
     * Indices are not preserved.
     *
     * @param mixed $element
     * @param int $every
     * @param int $startAt
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function intersperse($element, int $every = 1, int $startAt = 0): self;

    /**
     * Get the keys of the items.
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function keys(): self;

    /**
     * Get the last item.
     *
     * @return mixed
     */
    public function last();

    /**
     * Limit the first {$limit} items.
     *
     * @param int $limit
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function limit(int $limit): self;

    /**
     * Run a map over each of the items.
     *
     * @param callable ...$callbacks
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function map(callable ...$callbacks): self;

    /**
     * Push all of the given items onto the collection.
     *
     * @param iterable ...$sources
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function merge(...$sources): self;

    /**
     * Reset the keys on the underlying array.
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function normalize(): self;

    /**
     * Create a new collection consisting of every n-th element.
     *
     * @param  int  $step
     * @param  int  $offset
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function nth(int $step, int $offset = 0): self;

    /**
     * Get the items with the specified keys.
     *
     * @param mixed ...$keys
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function only(...$keys): self;

    /**
     * TODO: Pad.
     *
     * @param int $size
     * @param mixed $value
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function pad(int $size, $value): self;

    /**
     * @param array|string $pluck
     * @param null|mixed $default
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function pluck($pluck, $default = null): self;

    /**
     * @param string $method
     * @param string $proxyMethod
     * @param mixed ...$parameters
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function proxy(string $method, string $proxyMethod, ...$parameters): CollectionInterface;

    /**
     * @return \drupol\collection\Contract\Collection
     */
    public function rebase(): self;

    /**
     * Reduce the collection to a single value.
     *
     * @param callable $callback
     * @param mixed $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null);

    /**
     * @param \drupol\collection\Contract\Manipulator ...$operations
     *
     * @return bool|int|mixed
     */
    public function run(Manipulator ...$operations);

    /**
     * Skip the first {$count} items.
     *
     * @param int ...$counts
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function skip(int ...$counts): self;

    /**
     * Get a slice of items.
     *
     * @param int $offset
     * @param null|int $length
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function slice(int $offset, int $length = null): self;

    /**
     * Sort the collection using a callback.
     *
     * @param callable $callable
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function sort(callable $callable): self;

    /**
     * @param callable ...$callbacks
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function walk(callable ...$callbacks): self;

    /**
     * Zip the collection together with one or more arrays.
     *
     * e.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
     *      => [[1, 4], [2, 5], [3, 6]]
     *
     * @param mixed ...$items
     *
     * @return \drupol\collection\Contract\Collection
     */
    public function zip(...$items): self;
}
