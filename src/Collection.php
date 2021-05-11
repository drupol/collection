<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection;

use Closure;
use Iterator;
use loophp\collection\Contract\Collection as CollectionInterface;
use loophp\collection\Contract\Operation;
use loophp\collection\Iterator\ClosureIterator;
use loophp\collection\Iterator\IterableIterator;
use loophp\collection\Iterator\ResourceIterator;
use loophp\collection\Iterator\StringIterator;
use loophp\collection\Operation\Append;
use loophp\collection\Operation\Apply;
use loophp\collection\Operation\Associate;
use loophp\collection\Operation\AsyncMap;
use loophp\collection\Operation\Cache;
use loophp\collection\Operation\Chunk;
use loophp\collection\Operation\Coalesce;
use loophp\collection\Operation\Collapse;
use loophp\collection\Operation\Column;
use loophp\collection\Operation\Combinate;
use loophp\collection\Operation\Combine;
use loophp\collection\Operation\Compact;
use loophp\collection\Operation\Contains;
use loophp\collection\Operation\Current;
use loophp\collection\Operation\Cycle;
use loophp\collection\Operation\Diff;
use loophp\collection\Operation\DiffKeys;
use loophp\collection\Operation\Distinct;
use loophp\collection\Operation\Drop;
use loophp\collection\Operation\DropWhile;
use loophp\collection\Operation\Dump;
use loophp\collection\Operation\Duplicate;
use loophp\collection\Operation\Every;
use loophp\collection\Operation\Explode;
use loophp\collection\Operation\Falsy;
use loophp\collection\Operation\Filter;
use loophp\collection\Operation\First;
use loophp\collection\Operation\Flatten;
use loophp\collection\Operation\Flip;
use loophp\collection\Operation\FoldLeft;
use loophp\collection\Operation\FoldLeft1;
use loophp\collection\Operation\FoldRight;
use loophp\collection\Operation\FoldRight1;
use loophp\collection\Operation\Forget;
use loophp\collection\Operation\Frequency;
use loophp\collection\Operation\Get;
use loophp\collection\Operation\Group;
use loophp\collection\Operation\GroupBy;
use loophp\collection\Operation\Has;
use loophp\collection\Operation\Head;
use loophp\collection\Operation\IfThenElse;
use loophp\collection\Operation\Implode;
use loophp\collection\Operation\Init;
use loophp\collection\Operation\Inits;
use loophp\collection\Operation\Intersect;
use loophp\collection\Operation\IntersectKeys;
use loophp\collection\Operation\Intersperse;
use loophp\collection\Operation\Key;
use loophp\collection\Operation\Keys;
use loophp\collection\Operation\Last;
use loophp\collection\Operation\Limit;
use loophp\collection\Operation\Lines;
use loophp\collection\Operation\Map;
use loophp\collection\Operation\MatchOne;
use loophp\collection\Operation\Merge;
use loophp\collection\Operation\Normalize;
use loophp\collection\Operation\Nth;
use loophp\collection\Operation\Nullsy;
use loophp\collection\Operation\Pack;
use loophp\collection\Operation\Pad;
use loophp\collection\Operation\Pair;
use loophp\collection\Operation\Partition;
use loophp\collection\Operation\Permutate;
use loophp\collection\Operation\Pipe;
use loophp\collection\Operation\Pluck;
use loophp\collection\Operation\Prepend;
use loophp\collection\Operation\Product;
use loophp\collection\Operation\Random;
use loophp\collection\Operation\Range;
use loophp\collection\Operation\Reduction;
use loophp\collection\Operation\Reverse;
use loophp\collection\Operation\RSample;
use loophp\collection\Operation\Scale;
use loophp\collection\Operation\ScanLeft;
use loophp\collection\Operation\ScanLeft1;
use loophp\collection\Operation\ScanRight;
use loophp\collection\Operation\ScanRight1;
use loophp\collection\Operation\Shuffle;
use loophp\collection\Operation\Since;
use loophp\collection\Operation\Slice;
use loophp\collection\Operation\Sort;
use loophp\collection\Operation\Span;
use loophp\collection\Operation\Split;
use loophp\collection\Operation\Tail;
use loophp\collection\Operation\Tails;
use loophp\collection\Operation\TakeWhile;
use loophp\collection\Operation\Times;
use loophp\collection\Operation\Transpose;
use loophp\collection\Operation\Truthy;
use loophp\collection\Operation\Unfold;
use loophp\collection\Operation\Unlines;
use loophp\collection\Operation\Unpack;
use loophp\collection\Operation\Unpair;
use loophp\collection\Operation\Until;
use loophp\collection\Operation\Unwindow;
use loophp\collection\Operation\Unwords;
use loophp\collection\Operation\Unwrap;
use loophp\collection\Operation\Unzip;
use loophp\collection\Operation\Window;
use loophp\collection\Operation\Words;
use loophp\collection\Operation\Wrap;
use loophp\collection\Operation\Zip;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use const INF;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * @psalm-template TKey
 * @psalm-template TKey of array-key
 * @psalm-template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 *
 * @implements \loophp\collection\Contract\Collection<TKey, T>
 */
final class Collection implements CollectionInterface
{
    /**
     * @var array<int, mixed>
     * @psalm-var list<mixed>
     */
    private array $parameters;

    /**
     * @psalm-var callable(...mixed): (\Generator<TKey, T>|Iterator<TKey, T>)
     */
    private $source;

    /**
     * @param callable|Closure $callable
     * @psalm-param callable(...mixed): Iterator<TKey, T> $callable
     *
     * @param mixed ...$parameters
     * @psalm-param mixed ...$parameters
     */
    public function __construct(callable $callable, ...$parameters)
    {
        $this->source = $callable;
        $this->parameters = $parameters;
    }

    public function all(): array
    {
        return iterator_to_array($this->getIterator());
    }

    public function append(...$items): CollectionInterface
    {
        return new self(Append::of()(...$items), $this->getIterator());
    }

    public function apply(callable ...$callables): CollectionInterface
    {
        return new self(Apply::of()(...$callables), $this->getIterator());
    }

    public function associate(
        ?callable $callbackForKeys = null,
        ?callable $callbackForValues = null
    ): CollectionInterface {
        $defaultCallback =
            /**
             * @param mixed $carry
             * @psalm-param T|TKey $carry
             *
             * @psalm-return T|TKey
             */
            static fn ($carry) => $carry;

        return new self(Associate::of()($callbackForKeys ?? $defaultCallback)($callbackForValues ?? $defaultCallback), $this->getIterator());
    }

    public function asyncMap(callable ...$callbacks): CollectionInterface
    {
        return new self(AsyncMap::of()(...$callbacks), $this->getIterator());
    }

    public function cache(?CacheItemPoolInterface $cache = null): CollectionInterface
    {
        return new self(Cache::of()($cache ?? new ArrayAdapter()), $this->getIterator());
    }

    public function chunk(int ...$sizes): CollectionInterface
    {
        return new self(Chunk::of()(...$sizes), $this->getIterator());
    }

    public function coalesce(): CollectionInterface
    {
        return new self(Coalesce::of(), $this->getIterator());
    }

    public function collapse(): CollectionInterface
    {
        return new self(Collapse::of(), $this->getIterator());
    }

    public function column($column): CollectionInterface
    {
        return new self(Column::of()($column), $this->getIterator());
    }

    public function combinate(?int $length = null): CollectionInterface
    {
        return new self(Combinate::of()($length), $this->getIterator());
    }

    public function combine(...$keys): CollectionInterface
    {
        return new self(Combine::of()(...$keys), $this->getIterator());
    }

    public function compact(...$values): CollectionInterface
    {
        return new self(Compact::of()(...$values), $this->getIterator());
    }

    public function contains(...$value): CollectionInterface
    {
        return new self(Contains::of()(...$value), $this->getIterator());
    }

    public function count(): int
    {
        return iterator_count($this->getIterator());
    }

    public function current(int $index = 0)
    {
        return (new self(Current::of()($index), $this->getIterator()))->getIterator()->current();
    }

    public function cycle(): CollectionInterface
    {
        return new self(Cycle::of(), $this->getIterator());
    }

    public function diff(...$values): CollectionInterface
    {
        return new self(Diff::of()(...$values), $this->getIterator());
    }

    public function diffKeys(...$values): CollectionInterface
    {
        return new self(DiffKeys::of()(...$values), $this->getIterator());
    }

    public function distinct(): CollectionInterface
    {
        return new self(Distinct::of(), $this->getIterator());
    }

    public function drop(int ...$counts): CollectionInterface
    {
        return new self(Drop::of()(...$counts), $this->getIterator());
    }

    public function dropWhile(callable ...$callbacks): CollectionInterface
    {
        return new self(DropWhile::of()(...$callbacks), $this->getIterator());
    }

    public function dump(string $name = '', int $size = 1, ?Closure $closure = null): CollectionInterface
    {
        return new self(Dump::of()($name)($size)($closure), $this->getIterator());
    }

    public function duplicate(): CollectionInterface
    {
        return new self(Duplicate::of(), $this->getIterator());
    }

    /**
     * Create a new instance with no items.
     *
     * @psalm-template NewTKey
     * @psalm-template NewTKey of array-key
     * @psalm-template NewT
     */
    public static function empty(): CollectionInterface
    {
        return self::fromIterable([]);
    }

    public function every(callable ...$callbacks): CollectionInterface
    {
        return new self(Every::of()(static fn (): bool => false)(...$callbacks), $this->getIterator());
    }

    public function explode(...$explodes): CollectionInterface
    {
        return new self(Explode::of()(...$explodes), $this->getIterator());
    }

    public function falsy(): CollectionInterface
    {
        return new self(Falsy::of(), $this->getIterator());
    }

    public function filter(callable ...$callbacks): CollectionInterface
    {
        return new self(Filter::of()(...$callbacks), $this->getIterator());
    }

    public function first(): CollectionInterface
    {
        return new self(First::of(), $this->getIterator());
    }

    public function flatten(int $depth = PHP_INT_MAX): CollectionInterface
    {
        return new self(Flatten::of()($depth), $this->getIterator());
    }

    public function flip(): CollectionInterface
    {
        return new self(Flip::of(), $this->getIterator());
    }

    public function foldLeft(callable $callback, $initial = null): CollectionInterface
    {
        return new self(FoldLeft::of()($callback)($initial), $this->getIterator());
    }

    public function foldLeft1(callable $callback): CollectionInterface
    {
        return new self(FoldLeft1::of()($callback), $this->getIterator());
    }

    public function foldRight(callable $callback, $initial = null): CollectionInterface
    {
        return new self(Foldright::of()($callback)($initial), $this->getIterator());
    }

    public function foldRight1(callable $callback): CollectionInterface
    {
        return new self(FoldRight1::of()($callback), $this->getIterator());
    }

    public function forget(...$keys): CollectionInterface
    {
        return new self(Forget::of()(...$keys), $this->getIterator());
    }

    public function frequency(): CollectionInterface
    {
        return new self(Frequency::of(), $this->getIterator());
    }

    /**
     * @psalm-template NewTKey
     * @psalm-template NewTKey of array-key
     * @psalm-template NewT
     *
     * @param mixed ...$parameters
     */
    public static function fromCallable(callable $callable, ...$parameters): self
    {
        return new self($callable, ...$parameters);
    }

    public static function fromFile(string $filepath): self
    {
        return new self(
            /**
             * @psalm-return Iterator<int, string>
             */
            static fn (string $filepath): Iterator => new ResourceIterator(fopen($filepath, 'rb')),
            $filepath
        );
    }

    /**
     * @psalm-template NewTKey
     * @psalm-template NewTKey of array-key
     * @psalm-template NewT
     *
     * @param iterable<mixed> $iterable
     * @psalm-param iterable<NewTKey, NewT> $iterable
     */
    public static function fromIterable(iterable $iterable): self
    {
        return new self(
            /**
             * @psalm-param iterable<TKey, T> $iterable
             *
             * @psalm-return Iterator<TKey, T>
             */
            static fn (iterable $iterable): Iterator => new IterableIterator($iterable),
            $iterable
        );
    }

    /**
     * @param resource $resource
     */
    public static function fromResource($resource): self
    {
        return new self(
            /**
             * @param mixed $resource
             * @psalm-param resource $resource
             *
             * @psalm-return Iterator<int, string>
             */
            static fn ($resource): Iterator => new ResourceIterator($resource),
            $resource
        );
    }

    public static function fromString(string $string, string $delimiter = ''): self
    {
        return new self(
            /**
             * @psalm-return Iterator<int, string>
             */
            static fn (string $string, string $delimiter): Iterator => new StringIterator($string, $delimiter),
            $string,
            $delimiter
        );
    }

    public function get($key, $default = null): CollectionInterface
    {
        return new self(Get::of()($key)($default), $this->getIterator());
    }

    public function getIterator(): Iterator
    {
        return new ClosureIterator($this->source, ...$this->parameters);
    }

    public function group(): CollectionInterface
    {
        return new self(Group::of(), $this->getIterator());
    }

    public function groupBy(?callable $callable = null): CollectionInterface
    {
        return new self(GroupBy::of()($callable), $this->getIterator());
    }

    public function has(callable ...$callbacks): CollectionInterface
    {
        return new self(Has::of()(...$callbacks), $this->getIterator());
    }

    public function head(): CollectionInterface
    {
        return new self(Head::of(), $this->getIterator());
    }

    public function ifThenElse(callable $condition, callable $then, ?callable $else = null): CollectionInterface
    {
        $identity =
            /**
             * @psalm-param T $value
             *
             * @psalm-return T
             */
            static fn ($value) => $value;

        return new self(IfThenElse::of()($condition)($then)($else ?? $identity), $this->getIterator());
    }

    public function implode(string $glue = ''): CollectionInterface
    {
        return new self(Implode::of()($glue), $this->getIterator());
    }

    public function init(): CollectionInterface
    {
        return new self(Init::of(), $this->getIterator());
    }

    public function inits(): CollectionInterface
    {
        return new self(Inits::of(), $this->getIterator());
    }

    public function intersect(...$values): CollectionInterface
    {
        return new self(Intersect::of()(...$values), $this->getIterator());
    }

    public function intersectKeys(...$values): CollectionInterface
    {
        return new self(IntersectKeys::of()(...$values), $this->getIterator());
    }

    public function intersperse($element, int $every = 1, int $startAt = 0): CollectionInterface
    {
        return new self(Intersperse::of()($element)($every)($startAt), $this->getIterator());
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->all();
    }

    public function key(int $index = 0)
    {
        return (new self(Key::of()($index), $this->getIterator()))->getIterator()->current();
    }

    public function keys(): CollectionInterface
    {
        return new self(Keys::of(), $this->getIterator());
    }

    public function last(): CollectionInterface
    {
        return new self(Last::of(), $this->getIterator());
    }

    public function limit(int $count = -1, int $offset = 0): CollectionInterface
    {
        return new self(Limit::of()($count)($offset), $this->getIterator());
    }

    public function lines(): CollectionInterface
    {
        return new self(Lines::of(), $this->getIterator());
    }

    public function map(callable ...$callbacks): CollectionInterface
    {
        return new self(Map::of()(...$callbacks), $this->getIterator());
    }

    public function match(callable $callback, ?callable $matcher = null): CollectionInterface
    {
        // @todo: Rename this in next major version.
        // We cannot use Match::class because PHP 8 has
        // a new "match" function and we cannot use the same name.
        // @See https://github.com/loophp/collection/issues/56
        return new self(MatchOne::of()($matcher ?? static fn (): bool => true)($callback), $this->getIterator());
    }

    public function merge(iterable ...$sources): CollectionInterface
    {
        return new self(Merge::of()(...$sources), $this->getIterator());
    }

    public function normalize(): CollectionInterface
    {
        return new self(Normalize::of(), $this->getIterator());
    }

    public function nth(int $step, int $offset = 0): CollectionInterface
    {
        return new self(Nth::of()($step)($offset), $this->getIterator());
    }

    public function nullsy(): CollectionInterface
    {
        return new self(Nullsy::of(), $this->getIterator());
    }

    public function pack(): CollectionInterface
    {
        return new self(Pack::of(), $this->getIterator());
    }

    public function pad(int $size, $value): CollectionInterface
    {
        return new self(Pad::of()($size)($value), $this->getIterator());
    }

    public function pair(): CollectionInterface
    {
        return new self(Pair::of(), $this->getIterator());
    }

    public function partition(callable ...$callbacks): CollectionInterface
    {
        return new self(Partition::of()(...$callbacks), $this->getIterator());
    }

    public function permutate(): CollectionInterface
    {
        return new self(Permutate::of(), $this->getIterator());
    }

    public function pipe(callable ...$callables): self
    {
        return new self(Pipe::of()(...$callables), $this->getIterator());
    }

    public function pluck($pluck, $default = null): CollectionInterface
    {
        return new self(Pluck::of()($pluck)($default), $this->getIterator());
    }

    public function prepend(...$items): CollectionInterface
    {
        return new self(Prepend::of()(...$items), $this->getIterator());
    }

    public function product(iterable ...$iterables): CollectionInterface
    {
        return new self(Product::of()(...$iterables), $this->getIterator());
    }

    public function random(int $size = 1, ?int $seed = null): CollectionInterface
    {
        return new self(Random::of()($seed ?? random_int(PHP_INT_MIN, PHP_INT_MAX))($size), $this->getIterator());
    }

    public static function range(float $start = 0.0, float $end = INF, float $step = 1.0): CollectionInterface
    {
        return self::empty()->pipe(Range::of()($start)($end)($step));
    }

    public function reduction(callable $callback, $initial = null): CollectionInterface
    {
        return new self(Reduction::of()($callback)($initial), $this->getIterator());
    }

    public function reverse(): CollectionInterface
    {
        return new self(Reverse::of(), $this->getIterator());
    }

    public function rsample(float $probability): CollectionInterface
    {
        return new self(RSample::of()($probability), $this->getIterator());
    }

    public function scale(
        float $lowerBound,
        float $upperBound,
        float $wantedLowerBound = 0.0,
        float $wantedUpperBound = 1.0,
        float $base = 0.0
    ): CollectionInterface {
        return new self(Scale::of()($lowerBound)($upperBound)($wantedLowerBound)($wantedUpperBound)($base), $this->getIterator());
    }

    public function scanLeft(callable $callback, $initial = null): CollectionInterface
    {
        return new self(ScanLeft::of()($callback)($initial), $this->getIterator());
    }

    public function scanLeft1(callable $callback): CollectionInterface
    {
        return new self(ScanLeft1::of()($callback), $this->getIterator());
    }

    public function scanRight(callable $callback, $initial = null): CollectionInterface
    {
        return new self(ScanRight::of()($callback)($initial), $this->getIterator());
    }

    public function scanRight1(callable $callback): CollectionInterface
    {
        return new self(ScanRight1::of()($callback), $this->getIterator());
    }

    public function shuffle(?int $seed = null): CollectionInterface
    {
        return new self(Shuffle::of()($seed ?? random_int(PHP_INT_MIN, PHP_INT_MAX)), $this->getIterator());
    }

    public function since(callable ...$callbacks): CollectionInterface
    {
        return new self(Since::of()(...$callbacks), $this->getIterator());
    }

    public function slice(int $offset, int $length = -1): CollectionInterface
    {
        return new self(Slice::of()($offset)($length), $this->getIterator());
    }

    public function sort(int $type = Operation\Sortable::BY_VALUES, ?callable $callback = null): CollectionInterface
    {
        return new self(Sort::of()($type)($callback), $this->getIterator());
    }

    public function span(callable $callback): CollectionInterface
    {
        return new self(Span::of()($callback), $this->getIterator());
    }

    public function split(int $type = Operation\Splitable::BEFORE, callable ...$callbacks): CollectionInterface
    {
        return new self(Split::of()($type)(...$callbacks), $this->getIterator());
    }

    public function tail(): CollectionInterface
    {
        return new self(Tail::of(), $this->getIterator());
    }

    public function tails(): CollectionInterface
    {
        return new self(Tails::of(), $this->getIterator());
    }

    public function takeWhile(callable ...$callbacks): CollectionInterface
    {
        return new self(TakeWhile::of()(...$callbacks), $this->getIterator());
    }

    public static function times(int $number = 0, ?callable $callback = null): CollectionInterface
    {
        return self::empty()->pipe(Times::of()($number)($callback));
    }

    public function transpose(): CollectionInterface
    {
        return new self(Transpose::of(), $this->getIterator());
    }

    public function truthy(): CollectionInterface
    {
        return new self(Truthy::of(), $this->getIterator());
    }

    public static function unfold(callable $callback, ...$parameters): CollectionInterface
    {
        return self::empty()->pipe(Unfold::of()(...$parameters)($callback));
    }

    public function unlines(): CollectionInterface
    {
        return new self(Unlines::of(), $this->getIterator());
    }

    public function unpack(): CollectionInterface
    {
        return new self(Unpack::of(), $this->getIterator());
    }

    public function unpair(): CollectionInterface
    {
        return new self(Unpair::of(), $this->getIterator());
    }

    public function until(callable ...$callbacks): CollectionInterface
    {
        return new self(Until::of()(...$callbacks), $this->getIterator());
    }

    public function unwindow(): CollectionInterface
    {
        return new self(Unwindow::of(), $this->getIterator());
    }

    public function unwords(): CollectionInterface
    {
        return new self(Unwords::of(), $this->getIterator());
    }

    public function unwrap(): CollectionInterface
    {
        return new self(Unwrap::of(), $this->getIterator());
    }

    public function unzip(): CollectionInterface
    {
        return new self(Unzip::of(), $this->getIterator());
    }

    public function window(int $size): CollectionInterface
    {
        return new self(Window::of()($size), $this->getIterator());
    }

    public function words(): CollectionInterface
    {
        return new self(Words::of(), $this->getIterator());
    }

    public function wrap(): CollectionInterface
    {
        return new self(Wrap::of(), $this->getIterator());
    }

    public function zip(iterable ...$iterables): CollectionInterface
    {
        return new self(Zip::of()(...$iterables), $this->getIterator());
    }
}
