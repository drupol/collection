<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

include __DIR__ . '/../../vendor/autoload.php';

use loophp\collection\Collection;

/**
 * @param Collection<int, int> $collection
 */
function fromCallable_checkNumeric(Collection $collection): void
{
}
/**
 * @param Collection<string, int> $collection
 */
function fromCallable_checkMap(Collection $collection): void
{
}
/**
 * @param Collection<int, int|string> $collection
 */
function fromCallable_checkMixed(Collection $collection): void
{
}

/** @var Closure(): Generator<int, int> $generatorClosureNumeric */
$generatorClosureNumeric = static fn (): Generator => yield from range(1, 3);
/** @var Closure(): Generator<string, int> $generatorClosureMap */
$generatorClosureMap = static fn (): Generator => yield 'myKey' => 1;
/** @var Closure(): Generator<int, int|string> $generatorClosureMixed */
$generatorClosureMixed = static function (): Generator {
    yield 1 => 2;

    yield 3 => 'b';

    yield 1 => 'c';

    yield 4 => '5';
};

/** @var Closure(): array<int, int> $arrayNumeric */
$arrayNumeric = static fn (): array => range(1, 3);
/** @var Closure(): array<string, int> $arrayMap */
$arrayMap = static fn (): array => ['myKey' => 1];
/** @var Closure(): array<int, string|int> $arrayMixed */
$arrayMixed = static fn (): array => [1, 2, '3', 'b', 5];

/** @var Closure(): ArrayIterator<int, int> $arrayIteratorNumeric */
$arrayIteratorNumeric = static fn (int $a, int $b): ArrayIterator => new ArrayIterator(range($a, $b));
/** @var Closure(): ArrayIterator<string, int> $arrayIteratorMap */
$arrayIteratorMap = static fn (int $x): ArrayIterator => new ArrayIterator(['myKey' => $x]);
/** @var Closure(): ArrayIterator<int, string|int> $arrayIteratorMixed */
$arrayIteratorMixed = static fn (): ArrayIterator => new ArrayIterator([1, 2, '3', 5, 'b']);

$classWithMethod = new class() {
    /**
     * @return Generator<string, int>
     */
    public function getKeyValues(): Generator
    {
        yield 'myKey' => 1;
    }

    /**
     * @return Generator<int, int|string>
     */
    public function getMixed(): Generator
    {
        yield from [1, 2, '3', 'b', 5];
    }

    /**
     * @return Generator<int, int>
     */
    public function getValues(): Generator
    {
        yield from range(1, 5);
    }
};
$classWithStaticMethod = new class() {
    /**
     * @return Generator<string, int>
     */
    public static function getKeyValues(): Generator
    {
        yield 'myKey' => 1;
    }

    /**
     * @return Generator<int, int|string>
     */
    public static function getMixed(): Generator
    {
        yield from [1, 2, '3', 'b', 5];
    }

    /**
     * @return Generator<int, int>
     */
    public static function getValues(): Generator
    {
        return yield from range(1, 5);
    }
};
$invokableClassNumeric = new class() {
    /**
     * @return Generator<int, int>
     */
    public function __invoke(): Generator
    {
        yield from range(1, 5);
    }
};
$invokableClassMap = new class() {
    /**
     * @return Generator<string, int>
     */
    public function __invoke(): Generator
    {
        yield 'myKey' => 1;
    }
};
$invokableClassMixed = new class() {
    /**
     * @return Generator<int, int|string>
     */
    public function __invoke(): Generator
    {
        yield from [1, 2, '3', 'b', 5];
    }
};

fromCallable_checkNumeric(Collection::fromCallable($generatorClosureNumeric));
fromCallable_checkMap(Collection::fromCallable($generatorClosureMap));
fromCallable_checkMixed(Collection::fromCallable($generatorClosureMixed));

fromCallable_checkNumeric(Collection::fromCallable($arrayNumeric));
fromCallable_checkMap(Collection::fromCallable($arrayMap));
fromCallable_checkMixed(Collection::fromCallable($arrayMixed));

fromCallable_checkNumeric(Collection::fromCallable($arrayIteratorNumeric, 1, 3));
fromCallable_checkMap(Collection::fromCallable($arrayIteratorMap, 1));
fromCallable_checkMixed(Collection::fromCallable($arrayIteratorMixed));

fromCallable_checkNumeric(Collection::fromCallable([$classWithMethod, 'getValues']));
fromCallable_checkMap(Collection::fromCallable([$classWithMethod, 'getKeyValues']));
fromCallable_checkMixed(Collection::fromCallable([$classWithMethod, 'getMixed']));

fromCallable_checkNumeric(Collection::fromCallable([$classWithStaticMethod, 'getValues']));
fromCallable_checkMap(Collection::fromCallable([$classWithStaticMethod, 'getKeyValues']));
fromCallable_checkMixed(Collection::fromCallable([$classWithStaticMethod, 'getMixed']));

fromCallable_checkNumeric(Collection::fromCallable($invokableClassNumeric));
fromCallable_checkMap(Collection::fromCallable($invokableClassMap));
fromCallable_checkMixed(Collection::fromCallable($invokableClassMixed));
