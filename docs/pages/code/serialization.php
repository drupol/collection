<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

include __DIR__ . '/../../../vendor/autoload.php';

use loophp\collection\Collection;

// Example 1 -> using `json_encode`

// a) with list, ordered keys
json_encode(Collection::fromIterable([1, 2, 3])); // JSON: '[1, 2, 3]'

// b) with list, missing keys
$col = Collection::fromIterable([1, 2, 3])
    ->filter(static fn (int $val): bool => $val % 2 !== 0); // [0 => 1, 2 => 3]

json_encode($col); // JSON: '{"0": 1, "2": 3}'

// c) with list, missing keys, with `normalize`
$col = Collection::fromIterable([1, 2, 3])
    ->filter(static fn (int $val): bool => $val % 2 !== 0)
    ->normalize(); // [0 => 1, 1 => 3]

json_encode($col); // JSON: '[1, 3]'

// d) with associative array
json_encode(Collection::fromIterable(['foo' => 1, 'bar' => 2])); // JSON: '{"foo": 1, "bar": 2}'

// e) with associative array, with `normalize`

$col = Collection::fromIterable(['foo' => 1, 'bar' => 2])
    ->normalize(); // [0 => 1, 1 => 2]

json_encode($col); // JSON: '[1, 2]'

// Example 2 -> using custom serializer (all previous behaviors apply)

/** @var Symfony\Component\Serializer\Serializer $serializer */
$serializer = new Serializer(); // parameters omitted for brevity

$serializer->serialize(Collection::fromIterable([1, 2, 3]), 'json'); // JSON: '[1, 2, 3]'
