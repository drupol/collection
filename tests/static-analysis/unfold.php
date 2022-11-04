<?php

declare(strict_types=1);

include __DIR__ . '/../../vendor/autoload.php';

use loophp\collection\Collection;
use loophp\collection\Contract\Collection as CollectionInterface;

/**
 * @param CollectionInterface<int, int> $collection
 */
function unfold_checkList(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<int, list<int>> $collection
 */
function unfold_checkListOfLists(CollectionInterface $collection): void
{
}

$plusTwo = static fn (int $n = 0): array => [$n + 2];
$fib = static fn (int $a = 0, int $b = 1): array => [$b, $a + $b];

unfold_checkList(Collection::unfold($plusTwo)->unwrap());
unfold_checkList(Collection::unfold($plusTwo, [-2])->unwrap());

// VALID use cases -> PHPStan thinks the collection is of type Collection<int, array<int, mixed>>, but Psalm works

/** @psalm-suppress InvalidArgument @phpstan-ignore-next-line */
unfold_checkListOfLists(Collection::unfold($plusTwo, -2));
/**
 * @psalm-suppress InvalidArgument
 * @psalm-suppress TooManyArguments
 *
 * @phpstan-ignore-next-line
 */
unfold_checkListOfLists(Collection::unfold($fib, 0, 1));

// VALID use case -> `Pluck` can return various things so analysers cannot know the type is correct

/** @psalm-suppress InvalidArgument @phpstan-ignore-next-line */
unfold_checkList(Collection::unfold($fib)->pluck(0));

// INVALID use case -> parameters of different types

/** @psalm-suppress InvalidScalarArgument @phpstan-ignore-next-line */
unfold_checkListOfLists(Collection::unfold(static fn (int $a = 0, float $b = 1): array => [$b, $a + $b]));

// INVALID use case -> returning list of different type than the closure parameter

/** @psalm-suppress InvalidScalarArgument @phpstan-ignore-next-line */
unfold_checkListOfLists(Collection::unfold(static fn (int $n = 0): array => [(string) ($n + 2)]));
