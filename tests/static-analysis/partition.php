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
 * @param CollectionInterface<int, CollectionInterface<int, int>> $collection
 */
function partition_checkListCollectionInt(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<int, int> $collection
 */
function partition_checkListInt(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<int, CollectionInterface<string, string>> $collection
 */
function partition_checkMapCollectionString(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<string, string> $collection
 */
function partition_checkMapString(CollectionInterface $collection): void
{
}

// TODO: Replace this with loophp/typed-generators when it's done.
$integers = static function (int $i = 0): Generator {
    /** @phpstan-ignore-next-line */
    while (true) {
        yield $i++;
    }
};

function takeInt(int $int): void
{
}
function takeIntOrNull(?int $int): void
{
}
function takeString(string $string): void
{
}

$intValueCallback = static fn (int $value): bool => $value % 2 === 0;
$stringValueCallback = static fn (string $value): bool => 'bar' === $value;

partition_checkListCollectionInt(Collection::fromIterable($integers())->partition($intValueCallback));
partition_checkListCollectionInt(Collection::fromIterable($integers())->partition($intValueCallback, $intValueCallback));
partition_checkMapCollectionString(Collection::fromIterable(['foo' => 'bar', 'bar' => 'foo'])->partition($stringValueCallback));
partition_checkMapCollectionString(Collection::fromIterable(['foo' => 'bar', 'bar' => 'foo'])->partition($stringValueCallback, $stringValueCallback));

[$left, $right] = Collection::fromIterable($integers())->partition($intValueCallback)->all();
partition_checkListInt($left);
partition_checkListInt($right);

$first = Collection::fromIterable($integers())->partition($intValueCallback)->first();
$last = Collection::fromIterable($integers())->partition($intValueCallback)->last();
partition_checkListInt($first);
partition_checkListCollectionInt($last);

// VALID failures -> current returns T|null

/** @psalm-suppress PossiblyNullArgument @phpstan-ignore-next-line */
partition_checkListInt($last->current());

[$left, $right] = Collection::fromIterable(['foo' => 'bar', 'bar' => 'foo'])->partition($stringValueCallback)->all();
partition_checkMapString($left);
partition_checkMapString($right);

$first = Collection::fromIterable(['foo' => 'bar', 'bar' => 'foo'])->partition($stringValueCallback)->first();
$last = Collection::fromIterable(['foo' => 'bar', 'bar' => 'foo'])->partition($stringValueCallback)->last();
partition_checkMapString($first);
partition_checkMapCollectionString($last);

// VALID failures -> current returns T|null

/** @psalm-suppress PossiblyNullArgument @phpstan-ignore-next-line */
partition_checkMapString($last->current());
