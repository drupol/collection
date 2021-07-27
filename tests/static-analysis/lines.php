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
 * @param CollectionInterface<int, string> $collection
 */
function lines_checkListString(CollectionInterface $collection): void
{
}

/**
 * @param CollectionInterface<int, int> $collection
 */
function lines_checkListInt(CollectionInterface $collection): void
{
}

/**
 * @param CollectionInterface<string, string> $collection
 */
function lines_checkMapStringString(CollectionInterface $collection): void
{
}

lines_checkListString(Collection::fromIterable(range('a', 'e'))->lines());
lines_checkMapStringString(Collection::fromIterable(array_combine(range('a', 'c'), range('a', 'c')))->lines());

// VALID failure -> `lines` returns collection of string
/** @psalm-suppress InvalidScalarArgument @phpstan-ignore-next-line */
lines_checkListInt(Collection::fromIterable(range(0, 5))->lines());
