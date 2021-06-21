<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

include __DIR__ . '/../../vendor/autoload.php';

use loophp\collection\Collection;
use loophp\collection\Contract\Collection as CollectionInterface;

/**
 * @param CollectionInterface<int, int> $collection
 */
function squash_checkList(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<int, string> $collection
 */
function squash_checkStringList(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<string, string> $collection
 */
function squash_checkMap(CollectionInterface $collection): void
{
}

squash_checkList(Collection::fromIterable([1, 2, 3])->squash());
squash_checkMap(Collection::fromIterable(['foo' => 'f', 'bar' => 'b'])->squash());
squash_checkStringList(Collection::fromIterable(['a', 'b', 'c'])->squash());

// These work because `normalize` always changes the key to `int`
squash_checkList(Collection::fromIterable(['foo' => 1, 'bar' => 2])->normalize()->squash());
squash_checkStringList(Collection::fromIterable(['foo' => 'f', 'bar' => 'b'])->normalize()->squash());

// VALID failures -> `squash` does not change the key and value types
/** @psalm-suppress InvalidScalarArgument @phpstan-ignore-next-line */
squash_checkList(Collection::fromIterable(['a', 'b', 'c'])->squash());
/** @psalm-suppress InvalidScalarArgument @phpstan-ignore-next-line */
squash_checkStringList(Collection::fromIterable(['foo' => 'f', 'bar' => 'b'])->squash());
/** @psalm-suppress InvalidScalarArgument @phpstan-ignore-next-line */
squash_checkMap(Collection::fromIterable(['foo' => 1, 'bar' => 2])->squash());
