<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

include __DIR__ . '/../../../vendor/autoload.php';

use loophp\collection\CollectionT;

/**
 * @param CollectionT<int, int> $collection
 */
function fromIterableT_checkList(CollectionT $collection): void
{
}
/**
 * @param CollectionT<string, int> $collection
 */
function fromIterableT_checkMap(CollectionT $collection): void
{
}
/**
 * @param CollectionT<int, int|string> $collection
 */
function fromIterableT_checkMixed(CollectionT $collection): void
{
}

/** @var Closure(): Generator<int, int> $generatorList */
$generatorList = static fn (): Generator => yield from range(1, 3);
/** @var Closure(): Generator<string, int> $generatorMap */
$generatorMap = static fn (): Generator => yield 'myKey' => 1;
/** @var Closure(): Generator<int, int|string> $generatorMixed */
$generatorMixed = static function (): Generator {
    yield 1 => 2;

    yield 3 => 'b';

    yield 1 => 'c';

    yield 4 => '5';
};

/** @var array<int, int> $arrayList */
$arrayList = range(1, 3);
/** @var array<string, int> $arrayMap */
$arrayMap = ['foo' => 1, 'bar' => 2];
$arrayMixed = [1, 2, '3', 'b', 5];

/** @var ArrayIterator<int, int> $arrayIteratorList */
$arrayIteratorList = new ArrayIterator(range(1, 3));
/** @var ArrayIterator<string, int> $arrayIteratorMap */
$arrayIteratorMap = new ArrayIterator(['foo' => 1, 'bar' => 2]);
/** @var ArrayIterator<int, int|string> $arrayIteratorMixed */
$arrayIteratorMixed = new ArrayIterator([1, 2, '3', 'b', 5]);

fromIterableT_checkList(CollectionT::fromIterable($generatorList()));
fromIterableT_checkMap(CollectionT::fromIterable($generatorMap()));
fromIterableT_checkMixed(CollectionT::fromIterable($generatorMixed()));

fromIterableT_checkList(CollectionT::fromIterable($arrayList));
fromIterableT_checkMap(CollectionT::fromIterable($arrayMap));
fromIterableT_checkMixed(CollectionT::fromIterable($arrayMixed));

fromIterableT_checkList(CollectionT::fromIterable($arrayIteratorList));
fromIterableT_checkMap(CollectionT::fromIterable($arrayIteratorMap));
fromIterableT_checkMixed(CollectionT::fromIterable($arrayIteratorMixed));
