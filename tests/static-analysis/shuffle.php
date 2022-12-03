<?php

declare(strict_types=1);

include __DIR__ . '/../../vendor/autoload.php';

use loophp\collection\Collection;
use loophp\collection\Contract\Collection as CollectionInterface;

/**
 * @phpstan-param CollectionInterface<int, int> $collection
 *
 * @psalm-param CollectionInterface<int, int<0, max>> $collection
 */
function shuffle_checkIntList(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<int, string> $collection
 */
function shuffle_checkStringList(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<int, bool> $collection
 */
function shuffle_checkBoolList(CollectionInterface $collection): void
{
}
/**
 * @param CollectionInterface<string, string> $collection
 */
function shuffle_checkStringMap(CollectionInterface $collection): void
{
}

$intGenerator = static function (): Generator {
    yield random_int(0, mt_getrandmax());
};

$boolGenerator = static function (): Generator {
    yield (0 === random_int(0, mt_getrandmax()) % 2) ? true : false;
};

$stringGenerator = static function (): Generator {
    yield chr(random_int(0, 255));
};

$stringStringGenerator = static function (): Generator {
    yield chr(random_int(0, 255)) => chr(random_int(0, 255));
};

shuffle_checkIntList(Collection::fromIterable($intGenerator())->shuffle());
shuffle_checkStringList(Collection::fromIterable($stringGenerator())->shuffle());
shuffle_checkBoolList(Collection::fromIterable($boolGenerator())->shuffle());
shuffle_checkStringMap(Collection::fromIterable($stringStringGenerator())->shuffle());
