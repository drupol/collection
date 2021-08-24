<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\collection\Iterator;

use AppendIterator;
use ArrayIterator;
use CachingIterator;
use Closure;
use InfiniteIterator;
use Iterator;
use LimitIterator;
use MultipleIterator;
use NoRewindIterator;

/**
 * @internal
 * @immutable
 *
 * @template TKey
 * @template T
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class IteratorFactory
{
    /** * @extends ProxyIterator<TKey, T>
     *
     * @pure
     */
    public static function appendIterator(): Closure
    {
        return static fn (Iterator $iterator1): Closure => static function (Iterator $iterator2) use ($iterator1): AppendIterator {
            $appendIterator = new AppendIterator();

            $appendIterator->append(new NoRewindIterator($iterator2));
            $appendIterator->append(new NoRewindIterator($iterator1));

            return $appendIterator;
        };
    }

    /**
     * @pure
     */
    public static function arrayIterator(): Closure
    {
        return static function (array $data): ArrayIterator {
            return new ArrayIterator($data);
        };
    }

    /**
     * @pure
     */
    public static function cachingIterator(): Closure
    {
        return static fn (int $flags): Closure => static fn (Iterator $iterator): CachingIterator => new CachingIterator($iterator, $flags);
    }

    /**
     * @pure
     */
    public static function infiniteIterator(): Closure
    {
        return static fn (Iterator $iterator): InfiniteIterator => new InfiniteIterator($iterator);
    }

    /**
     * @pure
     */
    public static function iterableIterator(): Closure
    {
        return static function (iterable $iterable): IterableIterator {
            return new IterableIterator($iterable);
        };
    }

    /**
     * @pure
     */
    public static function limitIterator(): Closure
    {
        return static fn (int $offset): Closure => static fn (int $limit): Closure => static fn (Iterator $iterator): LimitIterator => new LimitIterator($iterator, $offset, $limit);
    }

    /**
     * @pure
     */
    public static function multipleIterableIterator(): Closure
    {
        return static function (iterable $iterables): Iterator {
            $appendIterator = new AppendIterator();

            foreach ($iterables as $iterable) {
                $appendIterator->append(new NoRewindIterator(new IterableIterator($iterable)));
            }

            return $appendIterator;
        };
    }

    /**
     * @pure
     */
    public static function multipleIterators(): Closure
    {
        return
            /**
             * @param 0|1|2|3 $flags
             */
            static fn (int $flags): Closure => static function (iterable $iterables) use ($flags): MultipleIterator {
                $mit = new MultipleIterator($flags);

                foreach ($iterables as $iterator) {
                    $mit->attachIterator($iterator);
                }

                return $mit;
            };
    }

    /**
     * @pure
     */
    public static function multipleIteratorTest(): Closure
    {
        return
            /**
             * @param 0|1|2|3 $flags
             */
            static fn (int $flags): Closure => static fn (Iterator $iterator1): Closure => static function (Iterator $iterator2) use ($flags, $iterator1): MultipleIterator {
                $mit = new MultipleIterator($flags);

                $mit->attachIterator($iterator1);
                $mit->attachIterator($iterator2);

                return $mit;
            };
    }

    /**
     * @pure
     */
    public static function prependIterator(): Closure
    {
        return static fn (Iterator $iterator1): Closure => static function (Iterator $iterator2) use ($iterator1): AppendIterator {
            $appendIterator = new AppendIterator();

            $appendIterator->append($iterator1);
            $appendIterator->append($iterator2);

            return $appendIterator;
        };
    }

    /**
     * @pure
     */
    public static function randomIterator(): Closure
    {
        return static fn (?int $seed = null): Closure => static fn (Iterator $iterator): RandomIterator => new RandomIterator($iterator, $seed);
    }

    /**
     * @pure
     */
    public static function typedIterator(): Closure
    {
        return static fn (?callable $callback = null): Closure => static fn (Iterator $iterator): TypedIterator => new TypedIterator($iterator, $callback);
    }
}
