<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection;

use Closure;
use Doctrine\Common\Collections\Criteria;
use Generator;
use Iterator;
use loophp\collection\Contract\Collection as CollectionInterface;
use loophp\collection\Contract\Operation;
use loophp\collection\Iterator\ArrayCacheIterator;
use loophp\collection\Iterator\ClosureIterator;
use loophp\collection\Iterator\IterableIterator;
use loophp\collection\Iterator\ResourceIterator;
use loophp\collection\Iterator\StringIterator;
use loophp\collection\Operation\All;
use loophp\collection\Operation\Append;
use loophp\collection\Operation\Apply;
use loophp\collection\Operation\Associate;
use loophp\collection\Operation\AsyncMap;
use loophp\collection\Operation\AsyncMapN;
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
use loophp\collection\Operation\Equals;
use loophp\collection\Operation\Every;
use loophp\collection\Operation\Explode;
use loophp\collection\Operation\Falsy;
use loophp\collection\Operation\Filter;
use loophp\collection\Operation\First;
use loophp\collection\Operation\FlatMap;
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
use loophp\collection\Operation\IsEmpty;
use loophp\collection\Operation\Key;
use loophp\collection\Operation\Keys;
use loophp\collection\Operation\Last;
use loophp\collection\Operation\Limit;
use loophp\collection\Operation\Lines;
use loophp\collection\Operation\Map;
use loophp\collection\Operation\MapN;
use loophp\collection\Operation\Matching;
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
use loophp\collection\Operation\Reduce;
use loophp\collection\Operation\Reduction;
use loophp\collection\Operation\Reject;
use loophp\collection\Operation\Reverse;
use loophp\collection\Operation\RSample;
use loophp\collection\Operation\Same;
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
use loophp\collection\Operation\Strict;
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
use loophp\collection\Operation\When;
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
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 *
 * @implements \loophp\collection\Contract\Collection<TKey, T>
 */
final class Collection implements CollectionInterface
{
    /**
     * @var iterable<mixed>
     */
    private iterable $parameters;

    /**
     * @var callable(mixed ...$parameters): iterable<TKey, T>
     */
    private $source;

    /**
     * @psalm-external-mutation-free
     *
     * @param callable(mixed ...$parameters): iterable<TKey, T> $callable
     * @param iterable<array-key, mixed> $parameters
     */
    private function __construct(callable $callable, iterable $parameters = [])
    {
        $this->source = $callable;
        $this->parameters = $parameters;
    }

    public function all(): array
    {
        return (new All())()($this->getIterator());
    }

    public function append(...$items): CollectionInterface
    {
        return new self((new Append())()(...$items), [$this->getIterator()]);
    }

    public function apply(callable ...$callbacks): CollectionInterface
    {
        return new self((new Apply())()(...$callbacks), [$this->getIterator()]);
    }

    public function associate(
        ?callable $callbackForKeys = null,
        ?callable $callbackForValues = null
    ): CollectionInterface {
        $defaultCallback =
            /**
             * @param T|TKey $carry
             *
             * @return T|TKey
             */
            static fn ($carry) => $carry;

        return new self((new Associate())()($callbackForKeys ?? $defaultCallback)($callbackForValues ?? $defaultCallback), [$this->getIterator()]);
    }

    public function asyncMap(callable $callback): CollectionInterface
    {
        return new self((new AsyncMap())()($callback), [$this->getIterator()]);
    }

    public function asyncMapN(callable ...$callbacks): CollectionInterface
    {
        return new self((new AsyncMapN())()(...$callbacks), [$this->getIterator()]);
    }

    public function cache(?CacheItemPoolInterface $cache = null): CollectionInterface
    {
        return new self((new Cache())()($cache ?? new ArrayAdapter()), [$this->getIterator()]);
    }

    public function chunk(int ...$sizes): CollectionInterface
    {
        return new self((new Chunk())()(...$sizes), [$this->getIterator()]);
    }

    public function coalesce(): CollectionInterface
    {
        return new self((new Coalesce())(), [$this->getIterator()]);
    }

    public function collapse(): CollectionInterface
    {
        return new self((new Collapse())(), [$this->getIterator()]);
    }

    public function column($column): CollectionInterface
    {
        return new self((new Column())()($column), [$this->getIterator()]);
    }

    public function combinate(?int $length = null): CollectionInterface
    {
        return new self((new Combinate())()($length), [$this->getIterator()]);
    }

    public function combine(...$keys): CollectionInterface
    {
        return new self((new Combine())()(...$keys), [$this->getIterator()]);
    }

    public function compact(...$values): CollectionInterface
    {
        return new self((new Compact())()(...$values), [$this->getIterator()]);
    }

    public function contains(...$values): bool
    {
        return (new self((new Contains())()(...$values), [$this->getIterator()]))->getIterator()->current();
    }

    public function count(): int
    {
        return iterator_count($this->getIterator());
    }

    public function current(int $index = 0)
    {
        return (new self((new Current())()($index), [$this->getIterator()]))->getIterator()->current();
    }

    public function cycle(): CollectionInterface
    {
        return new self((new Cycle())(), [$this->getIterator()]);
    }

    public function diff(...$values): CollectionInterface
    {
        return new self((new Diff())()(...$values), [$this->getIterator()]);
    }

    public function diffKeys(...$keys): CollectionInterface
    {
        return new self((new DiffKeys())()(...$keys), [$this->getIterator()]);
    }

    public function distinct(?callable $comparatorCallback = null, ?callable $accessorCallback = null): CollectionInterface
    {
        $accessorCallback ??=
            /**
             * @param T $value
             * @param TKey $key
             *
             * @return T
             */
            static fn ($value, $key) => $value;

        $comparatorCallback ??=
            /**
             * @param T $left
             *
             * @return Closure(T): bool
             */
            static fn ($left): Closure =>
                /**
                 * @param T $right
                 */
                static fn ($right): bool => $left === $right;

        return new self((new Distinct())()($comparatorCallback)($accessorCallback), [$this->getIterator()]);
    }

    public function drop(int $count): CollectionInterface
    {
        return new self((new Drop())()($count), [$this->getIterator()]);
    }

    public function dropWhile(callable ...$callbacks): CollectionInterface
    {
        return new self((new DropWhile())()(...$callbacks), [$this->getIterator()]);
    }

    public function dump(string $name = '', int $size = 1, ?Closure $closure = null): CollectionInterface
    {
        return new self((new Dump())()($name)($size)($closure), [$this->getIterator()]);
    }

    public function duplicate(?callable $comparatorCallback = null, ?callable $accessorCallback = null): CollectionInterface
    {
        $accessorCallback ??=
            /**
             * @param T $value
             * @param TKey $key
             *
             * @return T
             */
            static fn ($value, $key) => $value;

        $comparatorCallback ??=
            /**
             * @param T $left
             *
             * @return Closure(T): bool
             */
            static fn ($left): Closure =>
                /**
                 * @param T $right
                 */
                static fn ($right): bool => $left === $right;

        return new self((new Duplicate())()($comparatorCallback)($accessorCallback), [$this->getIterator()]);
    }

    /**
     * Create a new instance with no items.
     *
     * @template NewTKey
     * @template NewT
     *
     * @return self<NewTKey, NewT>
     */
    public static function empty(): CollectionInterface
    {
        /** @var array<NewTKey, NewT> $emptyArray */
        $emptyArray = [];

        return self::fromIterable($emptyArray);
    }

    public function equals(CollectionInterface $other): bool
    {
        return (new self((new Equals())()($other->getIterator()), [$this->getIterator()]))->getIterator()->current();
    }

    public function every(callable ...$callbacks): bool
    {
        return (new self((new Every())()(static fn (): bool => false)(...$callbacks), [$this->getIterator()]))->getIterator()->current();
    }

    public function explode(...$explodes): CollectionInterface
    {
        return new self((new Explode())()(...$explodes), [$this->getIterator()]);
    }

    public function falsy(): bool
    {
        return (new self((new Falsy())(), [$this->getIterator()]))->getIterator()->current();
    }

    public function filter(callable ...$callbacks): CollectionInterface
    {
        return new self((new Filter())()(...$callbacks), [$this->getIterator()]);
    }

    public function first(): CollectionInterface
    {
        return new self((new First())(), [$this->getIterator()]);
    }

    public function flatMap(callable $callback): CollectionInterface
    {
        return new self((new FlatMap())()($callback), [$this->getIterator()]);
    }

    public function flatten(int $depth = PHP_INT_MAX): CollectionInterface
    {
        return new self((new Flatten())()($depth), [$this->getIterator()]);
    }

    public function flip(): CollectionInterface
    {
        return new self((new Flip())(), [$this->getIterator()]);
    }

    public function foldLeft(callable $callback, $initial = null): CollectionInterface
    {
        return new self((new FoldLeft())()($callback)($initial), [$this->getIterator()]);
    }

    public function foldLeft1(callable $callback): CollectionInterface
    {
        return new self((new FoldLeft1())()($callback), [$this->getIterator()]);
    }

    public function foldRight(callable $callback, $initial = null): CollectionInterface
    {
        return new self((new FoldRight())()($callback)($initial), [$this->getIterator()]);
    }

    public function foldRight1(callable $callback): CollectionInterface
    {
        return new self((new FoldRight1())()($callback), [$this->getIterator()]);
    }

    public function forget(...$keys): CollectionInterface
    {
        return new self((new Forget())()(...$keys), [$this->getIterator()]);
    }

    public function frequency(): CollectionInterface
    {
        return new self((new Frequency())(), [$this->getIterator()]);
    }

    /**
     * @pure
     *
     * @template NewTKey
     * @template NewT
     *
     * @param callable(mixed ...$parameters): iterable<NewTKey, NewT> $callable
     * @param iterable<mixed> $parameters
     *
     * @return self<NewTKey, NewT>
     */
    public static function fromCallable(callable $callable, iterable $parameters = []): self
    {
        return new self($callable, $parameters);
    }

    /**
     * @pure
     *
     * @return self<int, string>
     */
    public static function fromFile(string $filepath): self
    {
        return new self(
            static fn (string $filepath): Iterator => new ResourceIterator(fopen($filepath, 'rb'), true),
            [$filepath]
        );
    }

    /**
     * @pure
     *
     * @template NewTKey
     * @template NewT
     *
     * @param Generator<NewTKey, NewT> $generator
     *
     * @return self<NewTKey, NewT>
     */
    public static function fromGenerator(Generator $generator): self
    {
        return self::fromIterable(
            new ArrayCacheIterator(
                new ClosureIterator(
                    static function (Generator $generator): Generator {
                        while ($generator->valid()) {
                            yield $generator->key() => $generator->current();
                            $generator->next();
                        }
                    },
                    [$generator]
                )
            )
        );
    }

    /**
     * @pure
     *
     * @template NewTKey
     * @template NewT
     *
     * @param iterable<NewTKey, NewT> $iterable
     *
     * @return self<NewTKey, NewT>
     */
    public static function fromIterable(iterable $iterable): self
    {
        return new self(
            static fn (iterable $iterable): Iterator => new IterableIterator($iterable),
            [$iterable]
        );
    }

    /**
     * @pure
     *
     * @param resource $resource
     *
     * @return self<int, string>
     */
    public static function fromResource($resource): self
    {
        return new self(
            /**
             * @param resource $resource
             */
            static fn ($resource): Iterator => new ResourceIterator($resource),
            [$resource]
        );
    }

    /**
     * @pure
     *
     * @return self<int, string>
     */
    public static function fromString(string $string, string $delimiter = ''): self
    {
        return new self(
            static fn (string $string, string $delimiter): Iterator => new StringIterator($string, $delimiter),
            [$string, $delimiter]
        );
    }

    /**
     * @param TKey $key
     * @param T|null $default
     */
    public function get($key, $default = null): CollectionInterface
    {
        return new self((new Get())()($key)($default), [$this->getIterator()]);
    }

    /**
     * @return Iterator<TKey, T>
     */
    public function getIterator(): Iterator
    {
        return new ClosureIterator($this->source, $this->parameters);
    }

    public function group(): CollectionInterface
    {
        return new self((new Group())(), [$this->getIterator()]);
    }

    public function groupBy(?callable $callable = null): CollectionInterface
    {
        return new self((new GroupBy())()($callable), [$this->getIterator()]);
    }

    public function has(callable ...$callbacks): bool
    {
        return (new self((new Has())()(...$callbacks), [$this->getIterator()]))->getIterator()->current();
    }

    public function head(): CollectionInterface
    {
        return new self((new Head())(), [$this->getIterator()]);
    }

    public function ifThenElse(callable $condition, callable $then, ?callable $else = null): CollectionInterface
    {
        $identity =
            /**
             * @param T $value
             *
             * @return T
             */
            static fn ($value) => $value;

        return new self((new IfThenElse())()($condition)($then)($else ?? $identity), [$this->getIterator()]);
    }

    public function implode(string $glue = ''): CollectionInterface
    {
        return new self((new Implode())()($glue), [$this->getIterator()]);
    }

    public function init(): CollectionInterface
    {
        return new self((new Init())(), [$this->getIterator()]);
    }

    public function inits(): CollectionInterface
    {
        return new self((new Inits())(), [$this->getIterator()]);
    }

    public function intersect(...$values): CollectionInterface
    {
        return new self((new Intersect())()(...$values), [$this->getIterator()]);
    }

    public function intersectKeys(...$keys): CollectionInterface
    {
        return new self((new IntersectKeys())()(...$keys), [$this->getIterator()]);
    }

    public function intersperse($element, int $every = 1, int $startAt = 0): CollectionInterface
    {
        return new self((new Intersperse())()($element)($every)($startAt), [$this->getIterator()]);
    }

    public function isEmpty(): bool
    {
        return (new self((new IsEmpty())(), [$this->getIterator()]))->getIterator()->current();
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
        return (new self((new Key())()($index), [$this->getIterator()]))->getIterator()->current();
    }

    public function keys(): CollectionInterface
    {
        return new self((new Keys())(), [$this->getIterator()]);
    }

    public function last(): CollectionInterface
    {
        return new self((new Last())(), [$this->getIterator()]);
    }

    public function limit(int $count = -1, int $offset = 0): CollectionInterface
    {
        return new self((new Limit())()($count)($offset), [$this->getIterator()]);
    }

    public function lines(): CollectionInterface
    {
        return new self((new Lines())(), [$this->getIterator()]);
    }

    public function map(callable $callback): CollectionInterface
    {
        return new self((new Map())()($callback), [$this->getIterator()]);
    }

    public function mapN(callable ...$callbacks): CollectionInterface
    {
        return new self((new MapN())()(...$callbacks), [$this->getIterator()]);
    }

    public function match(callable $callback, ?callable $matcher = null): bool
    {
        return (new self((new MatchOne())()($matcher ?? static fn (): bool => true)($callback), [$this->getIterator()]))
            ->getIterator()
            ->current();
    }

    public function matching(Criteria $criteria): CollectionInterface
    {
        return new self((new Matching())()($criteria), [$this->getIterator()]);
    }

    public function merge(iterable ...$sources): CollectionInterface
    {
        return new self((new Merge())()(...$sources), [$this->getIterator()]);
    }

    public function normalize(): CollectionInterface
    {
        return new self((new Normalize())(), [$this->getIterator()]);
    }

    public function nth(int $step, int $offset = 0): CollectionInterface
    {
        return new self((new Nth())()($step)($offset), [$this->getIterator()]);
    }

    public function nullsy(): bool
    {
        return (new self((new Nullsy())(), [$this->getIterator()]))->getIterator()->current();
    }

    public function pack(): CollectionInterface
    {
        return new self((new Pack())(), [$this->getIterator()]);
    }

    public function pad(int $size, $value): CollectionInterface
    {
        return new self((new Pad())()($size)($value), [$this->getIterator()]);
    }

    public function pair(): CollectionInterface
    {
        return new self((new Pair())(), [$this->getIterator()]);
    }

    public function partition(callable ...$callbacks): CollectionInterface
    {
        // TODO: Move this docblock above closure when https://github.com/phpstan/phpstan/issues/3770 lands.
        $mapCallback =
            /**
             * @param array{0: (Closure(Iterator<TKey, T>): Generator<TKey, T>), 1: (array{0: Iterator<TKey, T>})} $partitionResult
             */
            static function (array $partitionResult): CollectionInterface {
                /**
                 * @var Closure(Iterator<TKey, T>): Generator<TKey, T> $callback
                 * @var array{0: Iterator<TKey, T>} $parameters
                 */
                [$callback, $parameters] = $partitionResult;

                return self::fromCallable($callback, $parameters);
            };

        return new self((new Pipe())()((new Partition())()(...$callbacks), (new Map())()($mapCallback)), [$this->getIterator()]);
    }

    public function permutate(): CollectionInterface
    {
        return new self((new Permutate())(), [$this->getIterator()]);
    }

    public function pipe(callable $callback): CollectionInterface
    {
        return new self(Pipe::ofTyped1($callback), [$this->getIterator()]);
    }

    public function pluck($pluck, $default = null): CollectionInterface
    {
        return new self((new Pluck())()($pluck)($default), [$this->getIterator()]);
    }

    public function prepend(...$items): CollectionInterface
    {
        return new self((new Prepend())()(...$items), [$this->getIterator()]);
    }

    public function product(iterable ...$iterables): CollectionInterface
    {
        return new self((new Product())()(...$iterables), [$this->getIterator()]);
    }

    public function random(int $size = 1, ?int $seed = null): CollectionInterface
    {
        return new self((new Random())()($seed ?? random_int(PHP_INT_MIN, PHP_INT_MAX))($size), [$this->getIterator()]);
    }

    public static function range(float $start = 0.0, float $end = INF, float $step = 1.0): CollectionInterface
    {
        return self::empty()->pipe((new Range())()($start)($end)($step));
    }

    public function reduce(callable $callback, $initial = null): CollectionInterface
    {
        return new self((new Reduce())()($callback)($initial), [$this->getIterator()]);
    }

    public function reduction(callable $callback, $initial = null): CollectionInterface
    {
        return new self((new Reduction())()($callback)($initial), [$this->getIterator()]);
    }

    public function reject(callable ...$callbacks): CollectionInterface
    {
        return new self((new Reject())()(...$callbacks), [$this->getIterator()]);
    }

    public function reverse(): CollectionInterface
    {
        return new self((new Reverse())(), [$this->getIterator()]);
    }

    public function rsample(float $probability): CollectionInterface
    {
        return new self((new RSample())()($probability), [$this->getIterator()]);
    }

    public function same(CollectionInterface $other, ?callable $comparatorCallback = null): bool
    {
        $comparatorCallback ??=
            /**
             * @param T $leftValue
             * @param TKey $leftKey
             *
             * @return Closure(T, TKey): bool
             */
            static fn ($leftValue, $leftKey): Closure =>
                /**
                 * @param T $rightValue
                 * @param TKey $rightKey
                 */
                static fn ($rightValue, $rightKey): bool => $leftValue === $rightValue && $leftKey === $rightKey;

        return (new self((new Same())()($other->getIterator())($comparatorCallback), [$this->getIterator()]))->getIterator()->current();
    }

    public function scale(
        float $lowerBound,
        float $upperBound,
        float $wantedLowerBound = 0.0,
        float $wantedUpperBound = 1.0,
        float $base = 0.0
    ): CollectionInterface {
        return new self((new Scale())()($lowerBound)($upperBound)($wantedLowerBound)($wantedUpperBound)($base), [$this->getIterator()]);
    }

    public function scanLeft(callable $callback, $initial = null): CollectionInterface
    {
        return new self((new ScanLeft())()($callback)($initial), [$this->getIterator()]);
    }

    public function scanLeft1(callable $callback): CollectionInterface
    {
        return new self((new ScanLeft1())()($callback), [$this->getIterator()]);
    }

    public function scanRight(callable $callback, $initial = null): CollectionInterface
    {
        return new self((new ScanRight())()($callback)($initial), [$this->getIterator()]);
    }

    public function scanRight1(callable $callback): CollectionInterface
    {
        return new self((new ScanRight1())()($callback), [$this->getIterator()]);
    }

    public function shuffle(?int $seed = null): CollectionInterface
    {
        return new self((new Shuffle())()($seed ?? random_int(PHP_INT_MIN, PHP_INT_MAX)), [$this->getIterator()]);
    }

    public function since(callable ...$callbacks): CollectionInterface
    {
        return new self((new Since())()(...$callbacks), [$this->getIterator()]);
    }

    public function slice(int $offset, int $length = -1): CollectionInterface
    {
        return new self((new Slice())()($offset)($length), [$this->getIterator()]);
    }

    public function sort(int $type = Operation\Sortable::BY_VALUES, ?callable $callback = null): CollectionInterface
    {
        return new self((new Sort())()($type)($callback), [$this->getIterator()]);
    }

    public function span(callable ...$callbacks): CollectionInterface
    {
        // TODO: Move this docblock above closure when https://github.com/phpstan/phpstan/issues/3770 lands.
        $mapCallback =
            /**
             * @param array{0: (Closure(Iterator<TKey, T>): Generator<TKey, T>), 1: (array{0: Iterator<TKey, T>})} $spanResult
             */
            static function (array $spanResult): CollectionInterface {
                /**
                 * @var Closure(Iterator<TKey, T>): Generator<TKey, T> $callback
                 * @var array{0: Iterator<TKey, T>} $parameters
                 */
                [$callback, $parameters] = $spanResult;

                return self::fromCallable($callback, $parameters);
            };

        return new self((new Pipe())()((new Span())()(...$callbacks), (new Map())()($mapCallback)), [$this->getIterator()]);
    }

    public function split(int $type = Operation\Splitable::BEFORE, callable ...$callbacks): CollectionInterface
    {
        return new self((new Split())()($type)(...$callbacks), [$this->getIterator()]);
    }

    public function squash(): CollectionInterface
    {
        return self::fromIterable($this->pack()->all())->unpack();
    }

    public function strict(?callable $callback = null): CollectionInterface
    {
        return new self((new Strict())()($callback), [$this->getIterator()]);
    }

    public function tail(): CollectionInterface
    {
        return new self((new Tail())(), [$this->getIterator()]);
    }

    public function tails(): CollectionInterface
    {
        return new self((new Tails())(), [$this->getIterator()]);
    }

    public function takeWhile(callable ...$callbacks): CollectionInterface
    {
        return new self((new TakeWhile())()(...$callbacks), [$this->getIterator()]);
    }

    public static function times(int $number = 0, ?callable $callback = null): CollectionInterface
    {
        return self::empty()->pipe((new Times())()($number)($callback));
    }

    public function transpose(): CollectionInterface
    {
        return new self((new Transpose())(), [$this->getIterator()]);
    }

    public function truthy(): bool
    {
        return (new self((new Truthy())(), [$this->getIterator()]))->getIterator()->current();
    }

    public static function unfold(callable $callback, ...$parameters): CollectionInterface
    {
        return self::empty()->pipe((new Unfold())()(...$parameters)($callback));
    }

    public function unlines(): CollectionInterface
    {
        return new self((new Unlines())(), [$this->getIterator()]);
    }

    public function unpack(): CollectionInterface
    {
        return new self((new Unpack())(), [$this->getIterator()]);
    }

    public function unpair(): CollectionInterface
    {
        return new self((new Unpair())(), [$this->getIterator()]);
    }

    public function until(callable ...$callbacks): CollectionInterface
    {
        return new self((new Until())()(...$callbacks), [$this->getIterator()]);
    }

    public function unwindow(): CollectionInterface
    {
        return new self((new Unwindow())(), [$this->getIterator()]);
    }

    public function unwords(): CollectionInterface
    {
        return new self((new Unwords())(), [$this->getIterator()]);
    }

    public function unwrap(): CollectionInterface
    {
        return new self((new Unwrap())(), [$this->getIterator()]);
    }

    public function unzip(): CollectionInterface
    {
        return new self((new Unzip())(), [$this->getIterator()]);
    }

    public function when(callable $predicate, callable $whenTrue, ?callable $whenFalse = null): CollectionInterface
    {
        $whenFalse ??=
            /**
             * @param Iterator<TKey, T> $collection
             *
             * @return iterable<TKey, T>
             */
            static fn (Iterator $collection): iterable => $collection;

        return new self((new When())()($predicate)($whenTrue)($whenFalse), [$this->getIterator()]);
    }

    public function window(int $size): CollectionInterface
    {
        return new self((new Window())()($size), [$this->getIterator()]);
    }

    public function words(): CollectionInterface
    {
        return new self((new Words())(), [$this->getIterator()]);
    }

    public function wrap(): CollectionInterface
    {
        return new self((new Wrap())(), [$this->getIterator()]);
    }

    public function zip(iterable ...$iterables): CollectionInterface
    {
        return new self((new Zip())()(...$iterables), [$this->getIterator()]);
    }
}
