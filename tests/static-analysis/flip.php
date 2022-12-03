<?php

declare(strict_types=1);

include __DIR__ . '/../../vendor/autoload.php';

use loophp\collection\Collection;
use loophp\collection\Contract\Collection as CollectionInterface;

/**
 * @phpstan-param CollectionInterface<int, int> $collection
 *
 * @psalm-param CollectionInterface<int<0, max>, int> $collection
 */
function flip_checkIntInt(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<int, string> $collection
 */
function flip_checkIntString(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<string, int> $collection
 */
function flip_checkStringInt(CollectionInterface $collection): void
{
}

$intIntGenerator = static function (): Generator {
    yield random_int(0, mt_getrandmax());
};

$intStringGenerator = static function (): Generator {
    yield chr(random_int(0, 255));
};

flip_checkIntInt(Collection::fromIterable($intIntGenerator())->flip());
flip_checkStringInt(Collection::fromIterable($intStringGenerator())->flip());
flip_checkIntString(Collection::fromIterable($intStringGenerator())->flip()->flip());
